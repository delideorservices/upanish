from agents.base_agent import BaseAgent
from typing import Dict, Any

class MathProblemAnalyzer(BaseAgent):
    def __init__(self, openai_service):
        super().__init__(
            openai_service=openai_service,
            role="Math Problem Analyzer",
            goal="Identify the type, complexity, and key mathematical concepts in homework problems",
            backstory="""You are an expert mathematician who specializes in analyzing math problems. 
            You can quickly identify the mathematical concepts, required techniques, and difficulty level of any problem.
            Your analysis helps other teaching agents provide appropriate guidance to students.""",
            verbose=True
        )
    
    def analyze_problem(self, problem_text: str) -> Dict[str, Any]:
        """Analyze a math problem and extract key information"""
        
        # Prompt for the LLM to analyze the problem
        prompt = f"""
        Analyze the following math problem:
        
        {problem_text}
        
        Provide the following information:
        1. Type of problem (e.g., arithmetic, algebra, geometry)
        2. Key mathematical concepts involved
        3. Difficulty rating (1-5)
        4. Required knowledge/skills to solve
        5. Recommended approach
        
        Format your response as a structured JSON object with these fields.
        """
        
        # Get response from OpenAI
        response = self.openai_service.get_completion(prompt)
        
        # Parse the JSON response
        try:
            analysis = json.loads(response)
            return analysis
        except json.JSONDecodeError:
            # If the response is not valid JSON, return a structured response anyway
            return {
                "type": "unknown",
                "concepts": ["general mathematics"],
                "difficulty": 3,
                "required_knowledge": ["basic mathematics"],
                "approach": "Analyze the problem step by step"
            }