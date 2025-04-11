from fastapi import WebSocket, WebSocketDisconnect
import asyncio
import json
import logging
from datetime import datetime
from typing import Dict, List, Any, Optional
import base64
import os
from services.openai_service import OpenAIService
from services.crew_factory import CrewFactory
from utils.problem_classifier import ProblemClassifier

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class ConnectionManager:
    def __init__(self):
        self.active_connections: Dict[str, WebSocket] = {}
        
    async def connect(self, websocket: WebSocket, session_id: str):
        await websocket.accept()
        self.active_connections[session_id] = websocket
        logger.info(f"WebSocket connection established for session {session_id}")
        
    def disconnect(self, session_id: str):
        if session_id in self.active_connections:
            del self.active_connections[session_id]
            logger.info(f"WebSocket connection closed for session {session_id}")
            
    async def send_json(self, session_id: str, data: Dict):
        if session_id in self.active_connections:
            await self.active_connections[session_id].send_json(data)
            
    async def send_text(self, session_id: str, text: str):
        if session_id in self.active_connections:
            await self.active_connections[session_id].send_text(text)
            
    async def broadcast(self, message: str):
        for connection in self.active_connections.values():
            await connection.send_text(message)

connection_manager = ConnectionManager()

class SessionStore:
    def __init__(self):
        self.sessions: Dict[str, Dict[str, Any]] = {}
        
    def create_session(self, session_id: str, user_info: Dict[str, Any] = None):
        self.sessions[session_id] = {
            "messages": [],
            "user_info": user_info or {},
            "created_at": datetime.now(),
            "last_active": datetime.now()
        }
        return self.sessions[session_id]
        
    def get_session(self, session_id: str) -> Optional[Dict[str, Any]]:
        return self.sessions.get(session_id)
        
    def update_session(self, session_id: str, data: Dict[str, Any]):
        if session_id in self.sessions:
            self.sessions[session_id].update(data)
            self.sessions[session_id]["last_active"] = datetime.now()
            return self.sessions[session_id]
        return None
        
    def add_message(self, session_id: str, message: Dict[str, Any]):
        if session_id in self.sessions:
            if "messages" not in self.sessions[session_id]:
                self.sessions[session_id]["messages"] = []
                
            self.sessions[session_id]["messages"].append(message)
            self.sessions[session_id]["last_active"] = datetime.now()
            return True
        return False
        
    def get_conversation_history(self, session_id: str, limit: int = 10) -> List[Dict[str, Any]]:
        if session_id in self.sessions and "messages" in self.sessions[session_id]:
            return self.sessions[session_id]["messages"][-limit:]
        return []

session_store = SessionStore()

class WebSocketHandler:
    def __init__(self, openai_service: OpenAIService, crew_factory: CrewFactory, problem_classifier: ProblemClassifier):
        self.openai_service = openai_service
        self.crew_factory = crew_factory
        self.problem_classifier = problem_classifier
        
    async def handle_connection(self, websocket: WebSocket, session_id: str):
        await connection_manager.connect(websocket, session_id)
        
        # Check if session exists or create new one
        session = session_store.get_session(session_id)
        if not session:
            session = session_store.create_session(session_id)
            
        try:
            # Send welcome message
            await connection_manager.send_json(session_id, {
                "action": "connection_established",
                "session_id": session_id,
                "timestamp": datetime.now().isoformat()
            })
            
            while True:
                # Wait for messages from the client
                data = await websocket.receive_text()
                await self.process_message(session_id, data)
                
        except WebSocketDisconnect:
            connection_manager.disconnect(session_id)
            logger.info(f"WebSocket client disconnected: {session_id}")
        except Exception as e:
            logger.error(f"Error handling WebSocket connection: {e}")
            try:
                await connection_manager.send_json(session_id, {
                    "action": "error",
                    "message": "An error occurred while processing your request",
                    "timestamp": datetime.now().isoformat()
                })
            except:
                pass
            connection_manager.disconnect(session_id)
            
    async def process_message(self, session_id: str, data_str: str):
        try:
            # Parse the incoming message
            data = json.loads(data_str)
            action = data.get("action", "")
            content = data.get("content", "")
            
            # Record the incoming message
            message = {
                "content": content,
                "sender": "student",
                "timestamp": datetime.now().isoformat(),
                "isComplete": True
            }
            session_store.add_message(session_id, message)
            
            # Handle file attachments
            attachment = None
            if "attachment" in data and data["attachment"]:
                attachment = self.process_attachment(data["attachment"])
                
            # Process user request
            if action == "ask":
                # Inform client that response generation has started
                await connection_manager.send_json(session_id, {
                    "action": "start_response",
                    "timestamp": datetime.now().isoformat()
                })
                
                # Get conversation history for context
                history = session_store.get_conversation_history(session_id)
                
                # Determine subject from session data
                session_data = session_store.get_session(session_id)
                subject_info = session_data.get("user_info", {}).get("subject_info", {})
                
                # If we don't have subject info, classify the problem
                if not subject_info:
                    # Classify the problem to determine the appropriate agent
                    subject = self.problem_classifier.classify(content)
                else:
                    subject = subject_info.get("subject", "general")
                
                # Create appropriate crew for the subject
                student_info = session_data.get("user_info", {}).get("student_info", {})
                student_age = student_info.get("age")
                student_level = student_info.get("level")
                
                crew = self.crew_factory.create_crew(
                    subject, 
                    student_age, 
                    student_level
                )
                
                # Create a streaming content generator
                async def content_stream():
                    # Create the content for the teacher response
                    teacher_message = {
                        "content": "",
                        "sender": "teacher",
                        "timestamp": datetime.now().isoformat(),
                        "isComplete": False
                    }
                    session_store.add_message(session_id, teacher_message)
                    
                    # Generate response with context
                    context = {
                        "history": history,
                        "attachment": attachment,
                        "subject": subject,
                        "student_info": student_info
                    }
                    
                    # Stream the response chunks
                    is_first_chunk = True
                    async for chunk in crew.run_streaming(content, context):
                        # Update the message with the new content
                        if is_first_chunk:
                            # Brief pause to simulate teacher thinking
                            await asyncio.sleep(0.5)
                            is_first_chunk = False
                            
                        # Send chunk to the client
                        await connection_manager.send_text(session_id, chunk)
                        
                        # Update the message in the session store
                        messages = session_data.get("messages", [])
                        if messages:
                            messages[-1]["content"] += chunk
                            
                        # Add a small delay to simulate natural typing
                        await asyncio.sleep(0.03)
                    
                    # Mark the message as complete
                    messages = session_data.get("messages", [])
                    if messages:
                        messages[-1]["isComplete"] = True
                        
                    # Inform client that response is complete
                    await connection_manager.send_json(session_id, {
                        "action": "end_response",
                        "timestamp": datetime.now().isoformat()
                    })
                
                # Start streaming content
                asyncio.create_task(content_stream())
                
            elif action == "feedback":
                # Handle user feedback on responses
                feedback_data = data.get("feedback", {})
                message_id = feedback_data.get("message_id")
                is_helpful = feedback_data.get("is_helpful", True)
                
                # Log the feedback for future model improvements
                logger.info(f"Received feedback for message {message_id}: {'helpful' if is_helpful else 'not helpful'}")
                
                # You could store this feedback in your database for analysis
                
                # Thank the user for the feedback
                await connection_manager.send_json(session_id, {
                    "action": "feedback_received",
                    "message": "Thank you for your feedback!",
                    "timestamp": datetime.now().isoformat()
                })
                
        except json.JSONDecodeError:
            logger.error(f"Invalid JSON data received: {data_str}")
            await connection_manager.send_json(session_id, {
                "action": "error",
                "message": "Invalid message format",
                "timestamp": datetime.now().isoformat()
            })
        except Exception as e:
            logger.error(f"Error processing message: {e}")
            await connection_manager.send_json(session_id, {
                "action": "error",
                "message": "An error occurred while processing your request",
                "timestamp": datetime.now().isoformat()
            })
    
    def process_attachment(self, attachment_data):
        """Process attachment data from the client"""
        try:
            filename = attachment_data.get("filename", "attachment")
            mime_type = attachment_data.get("mime_type", "application/octet-stream")
            data = attachment_data.get("data", "")
            
            # Decode base64 data
            decoded_data = base64.b64decode(data)
            
            # Create uploads directory if it doesn't exist
            upload_dir = "uploads"
            os.makedirs(upload_dir, exist_ok=True)
            
            # Generate a unique filename
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            unique_filename = f"{upload_dir}/{timestamp}_{filename}"
            
            # Save the file
            with open(unique_filename, "wb") as f:
                f.write(decoded_data)
                
            return {
                "filename": filename,
                "path": unique_filename,
                "mime_type": mime_type,
                "size": len(decoded_data)
            }
        except Exception as e:
            logger.error(f"Error processing attachment: {e}")
            return None