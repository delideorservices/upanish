from fastapi import FastAPI, HTTPException, WebSocket, WebSocketDisconnect
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
import uvicorn
import os
from dotenv import load_dotenv
import json
import logging
import asyncio

# Import service modules
from services.openai_service import OpenAIService
from crews.crew_factory import CrewFactory
from services.response_formatter import ResponseFormatter
from utils.problem_classifier import ProblemClassifier

# Load environment variables
load_dotenv()

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI(title="UpanishAI Service")

# Initialize services
openai_service = OpenAIService(
    api_key=os.getenv("OPENAI_API_KEY")
)

problem_classifier = ProblemClassifier(openai_service)
crew_factory = CrewFactory(openai_service)
response_formatter = ResponseFormatter()

# Define request/response models
class HomeworkRequest(BaseModel):
    content: str
    file_path: Optional[str] = None
    subject_id: Optional[int] = None
    student_age: int
    student_level: int
    learning_style: str
    session_id: int

class HomeworkResponse(BaseModel):
    content: str
    explanation_level: int
    created_by_agent: str
    additional_resources: Optional[List[Dict[str, Any]]] = None

# WebSocket connection manager
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

# Session store for tracking conversation context
class SessionStore:
    def __init__(self):
        self.sessions: Dict[str, Dict[str, Any]] = {}
        
    def create_session(self, session_id: str, user_info: Dict[str, Any] = None):
        self.sessions[session_id] = {
            "messages": [],
            "user_info": user_info or {},
            "created_at": None,  # Will use string timestamps for JSON compatibility
            "last_active": None
        }
        return self.sessions[session_id]
        
    def get_session(self, session_id: str) -> Optional[Dict[str, Any]]:
        return self.sessions.get(session_id)
        
    def add_message(self, session_id: str, content: str, sender: str):
        if session_id not in self.sessions:
            self.create_session(session_id)
            
        if "messages" not in self.sessions[session_id]:
            self.sessions[session_id]["messages"] = []
                
        timestamp = None  # Would be datetime.now().isoformat()
        
        message = {
            "content": content,
            "sender": sender,
            "timestamp": timestamp
        }
            
        self.sessions[session_id]["messages"].append(message)
        self.sessions[session_id]["last_active"] = timestamp
        return message
        
    def get_conversation_history(self, session_id: str, limit: int = 10) -> List[Dict[str, Any]]:
        if session_id in self.sessions and "messages" in self.sessions[session_id]:
            return self.sessions[session_id]["messages"][-limit:]
        return []

# Create instances
connection_manager = ConnectionManager()
session_store = SessionStore()

@app.get("/")
async def root():
    return {"message": "UpanishAI Service is running"}

@app.post("/analyze-homework", response_model=HomeworkResponse)
async def analyze_homework(request: HomeworkRequest):
    try:
        logger.info(f"✅ Received homework analysis request for session {request.session_id}")
        print("📥 Request data:", request.dict())

        # 1. Classify the problem if subject not specified
        if not request.subject_id:
            subject = await problem_classifier.classify(request.content)
            logger.info(f"🧠 Classified subject: {subject}")
            print(f"🧠 Subject (auto-classified): {subject}")
        else:
            subject = "mathematics" if request.subject_id == 1 else "english"
            logger.info(f"📘 Subject (from ID): {subject}")
            print(f"📘 Subject (from ID): {subject}")

        print(f"👶 Student Age: {request.student_age}")
        print(f"🎓 Student Level: {request.student_level}")
        print(f"🎨 Learning Style: {request.learning_style}")

        # 2. Create appropriate crew based on subject
        crew = crew_factory.create_crew(
            subject=subject,
            student_age=request.student_age,
            student_level=request.student_level,
            learning_style=request.learning_style
        )
        logger.info("🛠️ Crew created successfully")
        print("🛠️ Crew created successfully")

        # 3. Run the crew to analyze the homework
        result = crew.run(request.content)
        logger.info("🧪 Crew analysis completed")
        print("🧪 Raw Crew Result:", result)

        # 4. Format the response for the appropriate age level
        formatted_response = response_formatter.format(
            result,
            age=request.student_age,
            learning_style=request.learning_style
        )

        print("✅ Final Formatted Response:", formatted_response)

        return HomeworkResponse(
            content=formatted_response["content"].raw if hasattr(formatted_response["content"], "raw") else str(formatted_response["content"]),
            explanation_level=formatted_response["explanation_level"],
            created_by_agent=formatted_response["created_by_agent"],
            additional_resources=formatted_response.get("additional_resources")
        )


    except Exception as e:
        logger.error(f"❌ Error analyzing homework: {str(e)}")
        print("❌ EXCEPTION:", str(e))
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/real-time-conversation")
async def real_time_conversation(request: HomeworkRequest):
    try:
        logger.info(f"🗣️ Real-time conversation started for session {request.session_id}")
        
        subject = "mathematics" if request.subject_id == 1 else "english"
        crew = crew_factory.create_crew(
            subject=subject,
            student_age=request.student_age,
            student_level=request.student_level,
            learning_style=request.learning_style
        )

        # Run the conversational agent (assuming only one response step)
        result = crew.run(request.content)

        # Just return plain string response for now
        return {
            "reply": result if isinstance(result, str) else str(result),
            "agent": crew.primary_agent_name if hasattr(crew, 'primary_agent_name') else "ai_teacher",
            "level": 1
        }

    except Exception as e:
        logger.error(f"❌ Error in real-time conversation: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.websocket("/ws/{session_id}")
async def websocket_endpoint(websocket: WebSocket, session_id: str):
    await connection_manager.connect(websocket, session_id)
    
    # Get or create session
    session = session_store.get_session(session_id)
    if not session:
        session = session_store.create_session(session_id)
    
    try:
        # Send welcome message
        await connection_manager.send_json(session_id, {
            "action": "connection_established",
            "session_id": session_id
        })
        
        while True:
            # Receive message from WebSocket
            data_str = await websocket.receive_text()
            data = json.loads(data_str)
            
            action = data.get("action", "")
            content = data.get("content", "")
            
            # Add student message to session
            session_store.add_message(session_id, content, "student")
            
            # Process based on action
            if action == "ask":
                # Inform client that response generation has started
                await connection_manager.send_json(session_id, {
                    "action": "start_response"
                })
                
                # Get user info from the message if available
                student_age = data.get("student_age")
                student_level = data.get("student_level")
                learning_style = data.get("learning_style")
                subject_id = data.get("subject_id")
                
                # Determine subject - either from message or by classification
                if subject_id:
                    subject = "mathematics" if subject_id == 1 else "english"
                else:
                    # Classify the problem
                    subject = await problem_classifier.classify(content)
                
                # Create appropriate crew
                crew = crew_factory.create_crew(
                    subject=subject,
                    student_age=student_age,
                    student_level=student_level,
                    learning_style=learning_style
                )
                
                # Get conversation history for context
                history = session_store.get_conversation_history(session_id)
                
                # Process response in streaming fashion
                async def stream_response():
                    # Simulate teacher thinking
                    await asyncio.sleep(0.5)
                    
                    # Start with initial pause
                    await connection_manager.send_text(session_id, "...")
                    
                    # Get full response - in a real implementation, this would
                    # be streamed token by token
                    full_response = crew.run(content, {"history": history})
                    
                    # Make sure response is a string
                    if not isinstance(full_response, str):
                        full_response = str(full_response)
                    
                    # Stream words one by one with slight delays for natural feel
                    teacher_message = ""
                    for word in full_response.split():
                        teacher_message += word + " "
                        await connection_manager.send_text(session_id, word + " ")
                        await asyncio.sleep(0.05)  # Slight delay for natural pacing
                    
                    # Store the complete teacher message
                    session_store.add_message(session_id, teacher_message.strip(), "teacher")
                    
                    # Signal completion
                    await connection_manager.send_json(session_id, {
                        "action": "end_response"
                    })
                
                # Start streaming in the background
                asyncio.create_task(stream_response())
            
            elif action == "feedback":
                # Handle feedback for improving the model
                feedback_data = data.get("feedback", {})
                is_helpful = feedback_data.get("is_helpful", True)
                
                logger.info(f"Received feedback: {'helpful' if is_helpful else 'not helpful'}")
                
                # Acknowledge feedback
                await connection_manager.send_json(session_id, {
                    "action": "feedback_received",
                    "message": "Thank you for your feedback!"
                })
            
    except WebSocketDisconnect:
        connection_manager.disconnect(session_id)
        logger.info(f"WebSocket client disconnected: {session_id}")
    except Exception as e:
        logger.error(f"WebSocket error: {str(e)}")
        try:
            await connection_manager.send_json(session_id, {
                "action": "error",
                "message": "An error occurred"
            })
        except:
            pass
        connection_manager.disconnect(session_id)

@app.get("/health")
async def health_check():
    return {"status": "healthy"}

if __name__ == "__main__":
    uvicorn.run("app:app", host="127.0.0.1", port=5000, reload=True)
