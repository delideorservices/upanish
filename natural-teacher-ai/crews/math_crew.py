from crewai import Crew, Task

class MathCrew:
    def __init__(self, agents):
        self.analyzer = agents.get('analyzer')
        self.solver = agents.get('solver')
        self.visualizer = agents.get('visualizer')
        self.practice_generator = agents.get('practice_generator')
        self.age_adapter = agents.get('age_adapter')
        self.engagement_specialist = agents.get('engagement_specialist')
        self.progress_tracker = agents.get('progress_tracker')
        self.gamification_agent = agents.get('gamification_agent')
        
        self.crew = self._create_crew()
    
    def _create_crew(self):
        # Define tasks
        analyze_task = Task(
            description="Analyze the mathematical problem to identify type, concepts, and difficulty",
            expected_output="A comprehensive analysis of the problem",
            agent=self.analyzer
        )
        
        solve_task = Task(
            description="Solve the problem step by step in a way that's clear and educational",
            expected_output="A step-by-step solution with explanation",
            agent=self.solver
        )
        
        visualize_task = Task(
            description="Create visual explanations for relevant mathematical concepts",
            expected_output="Visual representations to aid understanding",
            agent=self.visualizer
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
            description="Generate similar practice problems for further learning",
            expected_output="Related practice problems with solutions",
            agent=self.practice_generator
        )
        
        # Create the crew with a process
        crew = Crew(
            agents=[
                self.analyzer,
                self.solver,
                self.visualizer,
                self.age_adapter,
                self.engagement_specialist,
                self.practice_generator,
                self.progress_tracker,
                self.gamification_agent
            ],
            tasks=[
                analyze_task,
                solve_task,
                visualize_task,
                adapt_task,
                engage_task,
                practice_task
            ],
            verbose=True
        )
        
        return crew
    
    def run(self, math_problem):
        """Run the math crew to solve a problem"""
        result = self.crew.kickoff(inputs={"problem": math_problem})
        return result
