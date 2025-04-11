from agents.base_agent import BaseAgent
from typing import Dict, Any

class MathProblemAnalyzer(BaseAgent):
    def __init__(self, openai_service):
        super().__init__(openai_service, 
                        role="Math Problem Analyzer",
                        goal="Identify the type, complexity, and key mathematical concepts in homework problems")
                        
    def analyze_problem(self, problem_text: str) -> Dict[str, Any]:
        """Analyze a math problem and extract key information"""
        # Enhanced analysis with real-time feedback approach
        
        # First, assess the problem generally
        prompt = f"""
        Analyze the following math problem as a supportive math teacher would:
        
        {problem_text}
        
        Before solving, I want to help the student understand:
        1. What type of math problem this is (e.g., algebra, geometry)
        2. Key concepts needed to solve it
        3. An approach to break it down into manageable steps
        4. Where students typically get confused with this concept
        
        As a helpful teacher, provide a brief analysis that I could share with a student to help them get started.
        """
        
        response = self._openai_service.get_completion(prompt)
        
        # Parse the response
        try:
            analysis = json.loads(response)
        except json.JSONDecodeError:
            # If not valid JSON, create a structured response anyway
            analysis = {
                "type": "unknown",
                "concepts": ["general mathematics"],
                "difficulty": 3,
                "required_knowledge": ["basic mathematics"],
                "approach": "Analyze the problem step by step"
            }
            
        return analysis