class TeacherPersona:
    def __init__(self, persona_type):
        self.persona_type = persona_type
        self.traits = self._load_persona_traits(persona_type)
        
    def _load_persona_traits(self, persona_type):
        personas = {
            "supportive": {
                "tone": "warm and encouraging",
                "question_style": "guiding",
                "feedback_approach": "positive reinforcement",
                "explanation_depth": "thorough with examples"
            },
            "socratic": {
                "tone": "thoughtful and inquisitive",
                "question_style": "probing",
                "feedback_approach": "reflection-inducing",
                "explanation_depth": "gradually revealed through questions"
            },
            "pragmatic": {
                "tone": "clear and direct",
                "question_style": "straightforward",
                "feedback_approach": "direct with actionable steps",
                "explanation_depth": "concise and targeted"
            }
        }
        return personas.get(persona_type, personas["supportive"])
    
    def apply_persona_to_prompt(self, base_prompt):
        """Apply the persona traits to a base teaching prompt"""
        persona_instruction = f"""
        Respond as a {self.traits['tone']} teacher. 
        Ask {self.traits['question_style']} questions.
        Provide feedback using {self.traits['feedback_approach']}.
        Give explanations that are {self.traits['explanation_depth']}.
        """
        return base_prompt + "\n\n" + persona_instruction