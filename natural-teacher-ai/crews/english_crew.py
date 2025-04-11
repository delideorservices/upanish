from crewai import Crew, Task

class EnglishCrew:
    def __init__(self, agents):
        self.query_analyzer = agents.get('query_analyzer')
        self.english_analyzer = agents.get('english_analyzer')
        self.language_specialist = agents.get('language_specialist')
        self.reading_helper = agents.get('reading_helper')
        self.writing_coach = agents.get('writing_coach')
        self.practice_generator = agents.get('practice_generator')
        self.age_adapter = agents.get('age_adapter')
        self.engagement_specialist = agents.get('engagement_specialist')
        self.progress_tracker = agents.get('progress_tracker')
        self.gamification_agent = agents.get('gamification_agent')
        
        self.crew = self._create_crew()
    
    def _create_crew(self):
        # Define tasks
        greeting_and_analysis_task = Task(
            description="Greet student warmly, confirm what they want to learn, break grammar into fun steps if needed",
            expected_output="Greeting, topic confirmation, initial grammar explanation",
            agent=self.query_analyzer  # Add this agent at the top
        )

        analyze_task = Task(
            description="Analyze the language arts problem, identify key concepts and skills needed",
            expected_output="A comprehensive analysis of the language task",
            agent=self.language_specialist
        )
       
        reading_task = Task(
            description="If this is a reading comprehension question, analyze the passage and generate insights",
            expected_output="Reading comprehension guidance and analysis",
            agent=self.reading_helper
        )
        
        writing_task = Task(
            description="If this is a writing task, provide guidance on structure and composition",
            expected_output="Writing guidance and feedback",
            agent=self.writing_coach
        )
        
        adapt_task = Task(
            description="Adapt the explanations to be age-appropriate",
            expected_output="Age-appropriate content",
            agent=self.age_adapter
        )
        
        engage_task = Task(
            description="Enhance the content to be more engaging and interactive",
            expected_output="Engaging educational content",
            agent=self.engagement_specialist
        )
        
        practice_task = Task(
            description="Generate additional practice exercises or prompts",
            expected_output="Practice exercises with solutions",
            agent=self.practice_generator
        )
        
        # Create the crew with a process
        crew = Crew(
            agents=[
                self.english_analyzer,
                self.language_specialist,
                self.reading_helper,
                self.writing_coach,
                self.age_adapter,
                self.engagement_specialist,
                self.practice_generator,
                self.progress_tracker,
                self.gamification_agent
            ],
            tasks=[
                greeting_and_analysis_task,
                analyze_task,
                reading_task,
                writing_task,
                adapt_task,
                engage_task,
                practice_task
            ],
            verbose=True
        )
        
        return crew
    
    def run(self, english_problem):
        analysis = self.english_analyzer.analyze_input(english_problem)
        
        print("🎓", analysis.get("greeting"))
        print("🔍 Intent:", analysis.get("intent"))
        print("📚 Topic:", analysis.get("topic"))
        
        # Now continue with rest of crew
        result = self.crew.kickoff(inputs={
            "problem": english_problem,
            "intent": analysis.get("intent"),
            "topic": analysis.get("topic")
        })
        
        return result

