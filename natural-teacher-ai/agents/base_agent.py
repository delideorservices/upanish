from crewai import Agent
from typing import Dict, Any
from personalities.teacher_personas import TeacherPersona
from strategies.teaching_strategies import ScaffoldingStrategy, SocraticQuestioningStrategy, ExampleBasedStrategy

class BaseAgent(Agent):
    def __init__(self, openai_service, **kwargs):
        super().__init__(llm=openai_service, **kwargs)
        self._openai_service = openai_service
        
        self._persona = TeacherPersona(kwargs.get("persona_type", "supportive"))


        # Teaching strategies
        self.strategies = {
            "scaffolding": ScaffoldingStrategy(),
            "socratic": SocraticQuestioningStrategy(),
            "examples": ExampleBasedStrategy()
        }


    def run(self, inputs: Dict[str, Any]) -> str:
        problem = inputs.get("problem", "")
        context = inputs.get("context", {})
        history = context.get("history", [])

        strategy = self._select_teaching_strategy(context, problem)
        strategy_output = strategy.apply(context, problem)

        prompt = self._create_teaching_prompt(problem, strategy_output, context)
        final_prompt = self._persona.apply_persona_to_prompt(prompt)


        return self._openai_service.get_completion(final_prompt)

    def _select_teaching_strategy(self, context, problem):
        confusion = self._detect_confusion(context)
        complexity = self._assess_complexity(problem)
        stage = self._determine_learning_stage(context)

        if confusion > 0.7:
            return self.strategies["examples"]
        elif complexity > 0.8:
            return self.strategies["scaffolding"]
        else:
            return self.strategies["socratic"]

    def _create_teaching_prompt(self, problem, strategy_output, context):
        base = f"""
        You are a natural, empathetic teacher helping a student.

        Student's input: "{problem}"

        Teaching approach: {strategy_output['approach']}
        """

        if strategy_output['approach'] == 'scaffolding':
            base += f"\nBreak it into: {', '.join(strategy_output['steps'])}. Focus on: {strategy_output['current_step']}"
        elif strategy_output['approach'] == 'socratic':
            base += f"\nAsk these questions: {', '.join(strategy_output['questions'])}"
        elif strategy_output['approach'] == 'examples':
            base += f"\nExamples: {', '.join(strategy_output['examples'])}\nAnalogies: {', '.join(strategy_output['analogies'])}"

        if context.get("history"):
            base += "\n\nPrevious chat:\n"
            for msg in context["history"][-3:]:
                who = "Student" if msg["sender"] == "student" else "You (Teacher)"
                base += f"{who}: {msg['content']}\n"

        base += "\nRespond naturally. Be friendly, curious, helpful. Ask follow-up questions. Praise effort.\n"

        return base

    def _detect_confusion(self, context):
        return context.get("confusion_score", 0.5)  # Default mid if missing

    def _assess_complexity(self, problem):
        return 0.7 if len(problem.split()) > 20 else 0.3

    def _determine_learning_stage(self, context):
        return context.get("student_info", {}).get("stage", "beginner")
