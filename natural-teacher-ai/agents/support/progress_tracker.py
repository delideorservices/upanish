from agents.base_agent import BaseAgent

class ProgressTracker(BaseAgent):
    def __init__(self, openai_service, student_level=1):
        super().__init__(
            openai_service=openai_service,
            role="Progress Tracker",
            goal="Monitor student understanding and identify areas needing reinforcement",
            backstory="""You carefully observe student responses and homework solutions
            to identify strengths, weaknesses, and learning patterns. You provide insightful
            feedback to help students improve and grow.""",
            verbose=True
        )
        # self.student_level = student_level
    
    def assess_progress(self, homework_history, current_response):
        """Assess a student's progress based on homework history and current response"""
        
        prompt = f"""
        Analyze the student's progress based on:
        
        Previous homework results:
        {homework_history}
        
        Current response:
        {current_response}
        
        The student is currently at level {self.student_level}.
        
        Provide:
        1. Strengths demonstrated in the current response
        2. Areas that need improvement
        3. Specific recommendations for practice
        4. An overall assessment of progress
        """
        
        return self.openai_service.get_completion(prompt)
