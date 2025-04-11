from crewai import Agent
from typing import Dict, Any
import os
from personalities.teacher_personas import TeacherPersona
from strategies.teaching_strategies import ScaffoldingStrategy, SocraticQuestioningStrategy, ExampleBasedStrategy

class BaseAgent:
    def __init__(self, openai_service, **kwargs):
        self._init__(llm=openai_service, **kwargs)
        self._openai_service = openai_service
        
        # Initialize teaching personality
        self.persona = TeacherPersona(kwargs.get("persona_type", "supportive"))
        
        # Available teaching strategies
        self.strategies = {
            "scaffolding": ScaffoldingStrategy(),
            "socratic": SocraticQuestioningStrategy(),
            "examples": ExampleBasedStrategy()
        }
        
    def run(self, inputs: Dict[str, Any]) -> str:
        # Extract the problem from inputs
        problem = inputs.get("problem", "")
        
        # Check conversation context
        context = inputs.get("context", {})
        conversation_history = context.get("history", [])
        student_info = context.get("student_info", {})
        
        # Select appropriate teaching strategy based on context
        strategy = self._select_teaching_strategy(context, problem)
        strategy_output = strategy.apply(context, problem)
        
        # Create the teaching prompt
        prompt = self._create_teaching_prompt(problem, strategy_output, context)
        
        # Apply teacher persona characteristics
        final_prompt = self.persona.apply_persona_to_prompt(prompt)
        
        # Get response from LLM
        return self._openai_service.get_completion(final_prompt)
    
    def _select_teaching_strategy(self, context, problem):
        """Select the most appropriate teaching strategy for this interaction"""
        # Analyze various factors to choose strategy
        confusion_level = self._detect_confusion(context)
        concept_complexity = self._assess_complexity(problem)
        learning_stage = self._determine_learning_stage(context)
        
        if confusion_level > 0.7:
            return self.strategies["examples"]  # Use concrete examples for high confusion
        elif concept_complexity > 0.8:
            return self.strategies["scaffolding"]  # Break down complex topics
        else:
            return self.strategies["socratic"]  # Default to guided discovery
            
    def _create_teaching_prompt(self, problem, strategy_output, context):
        """Create a prompt that guides the LLM to respond like a natural teacher"""
        # Build base prompt with instructions to respond like a natural teacher
        base_prompt = f"""
        You are a natural, empathetic teacher helping a student with their question/assignment.
        
        Student's input: "{problem}"
        
        Teaching approach: {strategy_output['approach']}
        """
        
        # Add strategy-specific instructions
        if strategy_output['approach'] == 'scaffolding':
            base_prompt += f"""
            Break down your explanation into these steps: {', '.join(strategy_output['steps'])}
            Focus on step {strategy_output['current_step']} now.
            """
        elif strategy_output['approach'] == 'socratic':
            base_prompt += f"""
            Guide the student through discovery using these questions as inspiration:
            {', '.join(strategy_output['questions'])}
            """
        elif strategy_output['approach'] == 'examples':
            base_prompt += f"""
            Use these examples to illustrate the concept:
            {', '.join(strategy_output['examples'])}
            
            Consider these analogies:
            {', '.join(strategy_output['analogies'])}
            """
            
        # Add conversation context
        if context.get("history"):
            base_prompt += "\n\nPrevious conversation:\n"
            for msg in context["history"][-3:]:  # Last 3 messages
                sender = "Student" if msg["sender"] == "student" else "You (Teacher)"
                base_prompt += f"{sender}: {msg['content']}\n"
        
        # Add response guidance
        base_prompt += """
        Respond as a natural teacher would. Be conversational and encouraging.
        Ask questions to guide thinking when appropriate.
        Avoid overwhelming the student with too much information at once.
        Use simple language appropriate for the student's level.
        Include encouragement and praise for effort or correct understanding.
        """
        
        return base_prompt

