class ResponseFormatter:
    def format(self, response, age=10, learning_style="visual"):
        """Format AI response to be age-appropriate and match learning style"""
        
        # In a full implementation, this would adjust vocabulary, sentence length,
        # visuals, etc. based on age and learning style
        
        # For now, implement a simplified version
        explanation_level = 1 if age < 8 else (2 if age < 12 else 3)
        
        # If response is already a dictionary, enhance it
        if isinstance(response, dict):
            formatted = {
                "content": response.get("content", response),
                "explanation_level": explanation_level,
                "created_by_agent": response.get("created_by_agent", "AI Teacher"),
                "additional_resources": response.get("additional_resources", [])
            }
        else:
            # If response is a string, wrap it
            formatted = {
                "content": response,
                "explanation_level": explanation_level,
                "created_by_agent": "AI Teacher",
                "additional_resources": []
            }
            
        return formatted
