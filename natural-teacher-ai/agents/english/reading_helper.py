from agents.base_agent import BaseAgent

class ReadingComprehensionHelper(BaseAgent):
    def __init__(self, openai_service, student_level=1):
        super().__init__(
            openai_service=openai_service,
            role="Reading Comprehension Helper",
            goal="Help students understand and analyze reading passages",
            backstory="""You specialize in helping students extract meaning from texts.
            You excel at identifying main ideas, themes, character motivations, and literary devices,
            and can guide students to deeper understanding of what they read.""",
            verbose=True
        )
        # self.student_level = student_level
    
    def analyze_passage(self, passage):
        """Analyze a reading passage and provide insights"""
        
        # Adjust detail level based on student level
        detail_level = "basic" if self.student_level <= 2 else ("intermediate" if self.student_level <= 4 else "advanced")
        
        prompt = f"""
        Analyze the following passage:
        
        {passage}
        
        Provide a {detail_level} analysis including:
        1. Main idea or theme
        2. Key details or plot points
        3. Character analysis (if applicable)
        4. Literary devices used (if applicable)
        5. Context and implications
        
        Make your analysis appropriate for a student at level {self.student_level}.
        """
        
        return self.openai_service.get_completion(prompt)
    
    def generate_questions(self, passage):
        """Generate comprehension questions for a reading passage"""
        
        # Adjust question complexity based on student level
        if self.student_level <= 2:
            question_types = "factual recall and basic understanding"
        elif self.student_level <= 4:
            question_types = "inference, character motivation, and meaning"
        else:
            question_types = "theme analysis, critical evaluation, and author's intent"
        
        prompt = f"""
        Generate 5 reading comprehension questions for the following passage:
        
        {passage}
        
        Create questions that focus on {question_types}, appropriate for a student at level {self.student_level}.
        
        For each question, also provide:
        - The correct answer
        - An explanation of why it's correct
        - A hint the student could use if they're struggling
        """
        
        return self.openai_service.get_completion(prompt)
