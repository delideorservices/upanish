class ProblemClassifier:
    def __init__(self, openai_service):
        self.openai_service = openai_service
    
    async def classify(self, content):
        """Classify the subject of a homework problem"""
        
        prompt = f"""
        Classify the following homework question as either "mathematics" or "english":
        
        {content}
        
        Respond with just one word: either "mathematics" or "english".
        """
        
        response = self.openai_service.get_completion(prompt)
        
        # Normalize the response
        response = response.strip().lower()
        
        if "math" in response:
            return "mathematics"
        elif "english" in response:
            return "english"
        else:
            # Default to mathematics if unclear
            return "mathematics"
