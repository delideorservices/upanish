from agents.base_agent import BaseAgent

class MathPracticeGenerator(BaseAgent):
    def __init__(self, openai_service, student_level=1):
        super().__init__(
            openai_service=openai_service,
            role="Math Practice Generator",
            goal="Create similar practice problems to reinforce learning",
            backstory="""You create math practice problems that help students reinforce what they've learned.
            You carefully calibrate problem difficulty based on the student's level and create problems
            that target specific mathematical concepts.""",
            verbose=True
        )
        # self.student_level = student_level
    
    def generate_practice_problems(self, original_problem, concepts, count=3):
        """Generate similar practice problems based on the original problem"""
        
        prompt = f"""
        Create {count} practice problems similar to this one:
        
        {original_problem}
        
        These problems should:
        1. Focus on the concepts: {', '.join(concepts)}
        2. Be appropriate for a student at level {self.student_level}
        3. Gradually increase in difficulty
        4. Include the answer for each problem
        
        Format each problem with:
        - Problem: [the problem text]
        - Answer: [the answer]
        """
        
        return self.openai_service.get_completion(prompt)
