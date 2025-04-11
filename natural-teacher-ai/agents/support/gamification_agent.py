from agents.base_agent import BaseAgent

class GamificationAgent(BaseAgent):
    def __init__(self, openai_service, student_level=1, student_age=10):
        super().__init__(
            openai_service=openai_service,
            role="Gamification Specialist",
            goal="Enhance learning through game-like elements and rewards",
            backstory="""You transform educational experiences into engaging game-like journeys.
            You understand how to use points, badges, challenges, and rewards to motivate students
            and make learning more enjoyable.""",
            verbose=True
        )
        # self.student_level = student_level
        # self.student_age = student_age
    
    def suggest_achievements(self, completed_work, current_achievements):
        """Suggest achievements based on completed work"""
        
        prompt = f"""
        Based on the student's completed work and current achievements, suggest potential new achievements:
        
        Completed work:
        {completed_work}
        
        Current achievements:
        {current_achievements}
        
        The student is level {self.student_level} and is {self.student_age} years old.
        
        Suggest 2-3 specific achievements that would be:
        1. Challenging but attainable
        2. Relevant to their recent work
        3. Motivating and encourages further learning
        4. Age-appropriate
        
        Format each achievement with a name, description, and point value.
        """
        
        return self.openai_service.get_completion(prompt)
