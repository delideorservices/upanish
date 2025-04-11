from agents.base_agent import BaseAgent
import json
from typing import Dict, Any

class StepByStepSolver(BaseAgent):
    def __init__(self, openai_service, student_level=1):
        super().__init__(
            openai_service=openai_service,
            role="Step-by-Step Math Problem Solver",
            goal="Break down and solve math problems in clear, sequential steps tailored to the student's level",
            backstory="""You are an expert math tutor who excels at breaking down problems into easy-to-follow steps.
            You adjust your explanations based on the student's level and always encourage learning by providing clear rationale for each step.""",
            verbose=True
        )
        # self.student_level = student_level
    
    def solve_problem(self, problem_text: str, problem_type: str = None) -> Dict[str, Any]:
        """Solve a math problem step by step"""
        
        # Adjust detail level based on student level
        detail_level = "detailed" if self.student_level <= 2 else "concise"
        
        # Prompt for the LLM to solve the problem
        prompt = f"""
        Please solve this math problem step by step:
        
        {problem_text}
        
        Provide a {detail_level} solution that's appropriate for a student at level {self.student_level}.
        
        Format your answer with:
        1. Initial understanding of the problem
        2. Clear numbered steps
        3. Final answer clearly marked
        4. Brief explanation of the concepts involved
        
        Format your response as a structured JSON object with these fields:
        - steps: array of solution steps
        - final_answer: the answer to the problem
        - explanation: brief explanation of key concepts
        - difficulty_assessment: how challenging this problem is (1-5)
        """
        
        # Get response from OpenAI
        response = self.openai_service.get_completion(prompt)
        
        # Parse the JSON response
        try:
            solution = json.loads(response)
            return solution
        except json.JSONDecodeError:
            # If the response is not valid JSON, create a structured response anyway
            return {
                "steps": ["Parse the problem", "Apply relevant formulas", "Calculate the answer"],
                "final_answer": "Could not determine (please retry)",
                "explanation": "This problem requires understanding of basic mathematical concepts.",
                "difficulty_assessment": 3
            }
