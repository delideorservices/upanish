from crewai import Agent
from typing import Dict, Any
import os
class BaseAgent(Agent):
    def __init__(self, openai_service, **kwargs):
        super().__init__(llm=openai_service.get_llm(), **kwargs)
        self._openai_service = openai_service
        # self.student_age = student_age
        # self.learning_style = learning_style
    def run(self, inputs: Dict[str, Any]) -> str:
        prompt = inputs.get("problem", "")
        return self._openai_service.get_completion(prompt)


