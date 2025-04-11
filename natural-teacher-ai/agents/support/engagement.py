from agents.base_agent import BaseAgent

class EngagementSpecialist(BaseAgent):
    def __init__(self, openai_service, student_age=10, learning_style="visual"):
        super().__init__(
            openai_service=openai_service,
            role="Engagement Specialist",
            goal="Make learning fun and interactive for students",
            backstory="""You are an expert at making education engaging and fun.
            You know how to maintain student interest through interactive elements,
            relatable examples, and positive reinforcement.""",
            verbose=True
        )
        # self.student_age = student_age
        # self.learning_style = learning_style
    
    def enhance_engagement(self, content):
        """Add engaging elements to educational content"""
        
        prompt = f"""
        Enhance the following educational content to make it more engaging for a {self.student_age}-year-old 
        student with a {self.learning_style} learning style:
        
        {content}
        
        Add:
        1. A fun introduction that captures interest
        2. Interactive elements like questions or activities
        3. Relatable examples from everyday life
        4. Positive reinforcement and encouragement
        5. A brief closing that motivates further learning
        
        Maintain all the educational content and accuracy.
        """
        
        return self.openai_service.get_completion(prompt)
