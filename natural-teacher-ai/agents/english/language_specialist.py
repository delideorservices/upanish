from agents.base_agent import BaseAgent

class LanguageArtsSpecialist(BaseAgent):
    def __init__(self, openai_service):
        super().__init__(
            openai_service=openai_service,
            role="Language Arts Specialist",
            goal="Assist with grammar, vocabulary, and writing to improve language skills",
            backstory="""You are an expert in language arts with deep knowledge of grammar, 
            vocabulary, and composition. You excel at explaining language concepts clearly
            and helping students improve their writing and reading comprehension.""",
            verbose=True
        )
    
    def analyze_grammar(self, text):
        """Analyze grammar in a text and provide feedback"""
        
        prompt = f"""
        Analyze the grammar in the following text:
        
        {text}
        
        Provide feedback on:
        1. Grammar correctness
        2. Sentence structure
        3. Punctuation usage
        4. Potential improvements
        
        Explain each issue in a way that helps the student learn from their mistakes.
        """
        
        return self.openai_service.get_completion(prompt)
    
    def enhance_vocabulary(self, text, grade_level):
        """Suggest vocabulary improvements for a text"""
        
        prompt = f"""
        Analyze the vocabulary in the following text:
        
        {text}
        
        The text was written by a student at grade level {grade_level}.
        
        Suggest:
        1. 3-5 words that could be replaced with more precise or advanced alternatives
        2. For each suggestion, provide the replacement word, its definition, and a new sentence using it
        3. Ensure suggestions are appropriate for a student at grade level {grade_level}
        """
        
        return self.openai_service.get_completion(prompt)
    
    def explain_concept(self, concept):
        """Explain a language arts concept"""
        
        prompt = f"""
        Explain the following language arts concept:
        
        {concept}
        
        Provide:
        1. A clear definition
        2. Examples demonstrating the concept
        3. Common mistakes or misunderstandings
        4. Tips for mastering this concept
        """
        
        return self.openai_service.get_completion(prompt)
