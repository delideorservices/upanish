from crewai import Agent

class EnglishQueryAnalyzer(Agent):
    def __init__(self, openai_service):
        super().__init__(
            name="English Query Analyzer",
            role="Conversational teacher assistant that welcomes and identifies what the student wants to learn",
            goal=(
                "Greet the student warmly like a teacher. "
                "Ask a light follow-up to confirm if they want help with grammar, vocabulary, writing, or reading. "
                "If grammar is selected, break the topic down and guide in small steps like a fun teacher."
            ),
            backstory=(
                "You are a friendly language teacher for kids aged 6–11. "
                "You always begin with encouragement and gently guide students with simple, interactive questions. "
                "You adapt based on the student's responses and break things down in a fun way."
            ),
            verbose=True,
            allow_delegation=False,
            openai_service=openai_service
        )

    def run(self, inputs):
        question = inputs.get("problem", "")
        return (
            f"🌟 Hi there, superstar! I’m so happy you asked for help with English. "
            f"Can I ask—are you curious about grammar (like how sentences work), vocabulary (new words), writing (telling your ideas), or reading (understanding stories)?\n\n"
            f"From your question, it looks like you want to learn the *basics of grammar*. Yay! 🎉 Let's get started slowly—"
            f"Can you tell me what grammar means to you? Or should I start with a fun example of a sentence puzzle? 😊"
        )
