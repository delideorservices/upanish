from agents.base_agent import BaseAgent

class VisualMathExplainer(BaseAgent):
    def __init__(self, openai_service, student_age=10, learning_style="visual"):
        super().__init__(
            openai_service=openai_service,
            role="Visual Math Explainer",
            goal="Create visual explanations and representations of mathematical concepts",
            backstory="""You specialize in making math concepts easy to understand through visual aids.
            You create text-based visual representations like diagrams, charts, and illustrations
            to help students grasp abstract mathematical ideas.""",
            verbose=True
        )
        # self.student_age = student_age
        # self.learning_style = learning_style
    
    def create_visual_explanation(self, concept, problem_type=None):
        """Create a visual explanation for a math concept"""
        
        prompt = f"""
        Create a visual explanation for the following math concept:
        
        {concept}
        
        This explanation is for a {self.student_age}-year-old student with a {self.learning_style} learning style.
        
        Use ASCII art or text-based diagrams to illustrate the concept when possible.
        Make the explanation age-appropriate and engaging.
        """
        
        return self.openai_service.get_completion(prompt)
