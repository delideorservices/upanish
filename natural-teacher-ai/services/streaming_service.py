from fastapi import WebSocket
import asyncio

class StreamingResponseService:
    def __init__(self):
        self.active_connections = {}
        
    async def connect(self, websocket: WebSocket, session_id: str):
        await websocket.accept()
        self.active_connections[session_id] = websocket
        
    def disconnect(self, session_id: str):
        self.active_connections.pop(session_id, None)
        
    async def stream_response(self, session_id: str, response_generator):
        """Stream a response word-by-word to the client"""
        websocket = self.active_connections.get(session_id)
        if not websocket:
            return
            
        async for word in response_generator:
            await websocket.send_text(word)
            await asyncio.sleep(0.01)  #