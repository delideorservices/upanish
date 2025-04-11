from agents.base_agent import BaseAgent

class EnglishPracticeGenerator(BaseAgent):
    def __init__(self, openai_service, student_level=1):
        super().__init__(
            openai_service=openai_service,
            role="English Practice Generator",
            goal="Create customized language exercises and writing prompts",
            backstory="""You design engaging practice activities that help students
            strengthen their language arts skills. You create exercises that target
            specific skills while keeping students motivated and interested.""",
            verbose=True
        )
        # self.student_level = student_level
    
    def generate_writing_prompts(self, skill_focus, count=3):
        """Generate writing prompts focused on specific skills"""
        
        # Adjust prompt complexity based on student level
        if self.student_level <= 2:
            complexity = "simple prompts with clear directions"
        elif self.student_level <= 4:
            complexity = "moderately challenging prompts that encourage creativity"
        else:
            complexity = "thought-provoking prompts that require critical thinking"
        
        prompt = f"""
        Create {count} writing prompts that focus on {skill_focus}.
        
        These should be {complexity}, appropriate for a student at level {self.student_level}.
        
        For each prompt, include:
        1. The writing prompt itself
        2. The specific skills it practices
        3. A suggestion for how to approach the prompt
        4. An estimated word count target
        """
        
        return self.openai_service.get_completion(prompt)
    
    def create_language_exercises(self, skill_area, difficulty="medium", count=5):
        """Create language exercises for a specific skill area"""
        
        prompt = f"""
        Create {count} {difficulty}-difficulty language exercises focused on {skill_area}.
        
        These should be appropriate for a student at level {self.student_level}.
        
        For each exercise:
        1. Provide clear instructions
        2. Include the exercise content/questions
        3. Provide the answers/solutions
        4. Add a helpful tip for solving similar problems
        """
        
        return self.openai_service.get_completion(prompt)
