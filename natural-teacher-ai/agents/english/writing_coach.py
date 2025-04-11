from agents.base_agent import BaseAgent

class WritingCoach(BaseAgent):
    def __init__(self, openai_service, student_age=10):
        super().__init__(
            openai_service=openai_service,
            role="Writing Coach",
            goal="Provide guidance on writing structure and improve composition skills",
            backstory="""You are an experienced writing tutor who helps students develop
            their writing skills. You provide constructive feedback on structure, clarity,
            and style while encouraging creativity and growth.""",
            verbose=True
        )
        # self.student_age = student_age
    
    def provide_feedback(self, writing_sample):
        """Provide constructive feedback on a writing sample"""
        
        # Adjust feedback detail based on student age
        if self.student_age <= 7:
            focus_areas = "basic sentence structure, simple vocabulary, and clear ideas"
        elif self.student_age <= 10:
            focus_areas = "paragraph structure, descriptive language, and logical flow"
        else:
            focus_areas = "essay structure, coherent arguments, varied sentence structure, and stylistic choices"
        
        prompt = f"""
        Provide constructive feedback on the following writing sample:
        
        {writing_sample}
        
        This was written by a {self.student_age}-year-old student.
        
        Focus your feedback on {focus_areas}.
        
        Include:
        1. 2-3 specific strengths of the writing
        2. 2-3 areas that could be improved
        3. Specific suggestions for improvement
        4. An encouraging summary that motivates the student
        
        Make your feedback age-appropriate and supportive.
        """
        
        return self.openai_service.get_completion(prompt)
    
    def suggest_outline(self, topic, writing_type):
        """Suggest a writing outline based on a topic"""
        
        # Adjust outline complexity based on student age
        if self.student_age <= 7:
            structure = "beginning, middle, and end"
        elif self.student_age <= 10:
            structure = "introduction, 2-3 main points, and conclusion"
        else:
            structure = "introduction with thesis, 3-5 supporting paragraphs, and conclusion"
        
        prompt = f"""
        Create a writing outline for a {self.student_age}-year-old student on the topic:
        
        "{topic}"
        
        This will be a {writing_type} (e.g., story, essay, report).
        
        Provide an age-appropriate outline with {structure}.
        For each section, include:
        1. A brief description of what should be included
        2. 1-2 guiding questions to help the student develop ideas
        3. An example or suggestion to spark creativity
        """
        
        return self.openai_service.get_completion(prompt)
