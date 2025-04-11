from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
import uvicorn
import os
from dotenv import load_dotenv
import json
import logging

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
app = FastAPI(title="UpanishadAI AI Service")

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

@app.get("/")
async def root():
    return {"message": "UpanishadAI AI Service is running"}
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

@app.get("/health")
async def health_check():
    return {"status": "healthy"}

if __name__ == "__main__":
    uvicorn.run("app:app", host="127.0.0.1", port=5000, reload=True)