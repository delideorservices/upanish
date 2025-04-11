from agents.base_agent import BaseAgent
from typing import Dict, Any

class AgeAdaptationAgent(BaseAgent):
    def __init__(self, openai_service, student_age=10):
        super().__init__(
            openai_service=openai_service,
            role="Age Adaptation Specialist",
            goal="Adapt content to be age-appropriate",
            backstory="You're an expert at making things kid-friendly.",
            verbose=True
        )
        # self.student_age = student_age  # ✅ Must come AFTER super()

    def run(self, inputs: Dict[str, Any]) -> str:
        content = inputs.get("problem", "")
        return self.adapt_content(content)

    def adapt_content(self, content):
        prompt = f"""
        Adapt the following educational content to be appropriate for a {self.student_age}-year-old student:

        {content}

        Make sure to:
        1. Use vocabulary appropriate for the age
        2. Keep sentences shorter for younger children
        3. Use engaging language and examples
        4. Maintain all educational content and accuracy
        5. Include encouraging language for younger children
        """
        return self._openai_service.get_completion(prompt)
