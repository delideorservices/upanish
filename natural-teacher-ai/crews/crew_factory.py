from agents.math.analyzer import MathProblemAnalyzer
from agents.math.solver import StepByStepSolver
from agents.math.visualizer import VisualMathExplainer
from agents.math.practice_generator import MathPracticeGenerator
from agents.english.language_specialist import LanguageArtsSpecialist
from agents.english.query_analyzer import EnglishQueryAnalyzer
from agents.english.reading_helper import ReadingComprehensionHelper
from agents.english.writing_coach import WritingCoach
from agents.english.practice_generator import EnglishPracticeGenerator
from agents.support.age_adapter import AgeAdaptationAgent
from agents.support.engagement import EngagementSpecialist
from agents.support.progress_tracker import ProgressTracker
from agents.support.gamification_agent import GamificationAgent
from crews.math_crew import MathCrew
from crews.english_crew import EnglishCrew

class CrewFactory:
    def __init__(self, openai_service,student_age=10):
        self.openai_service = openai_service
        self.student_age=student_age
    def create_crew(self, subject: str, student_age: int, student_level: int, learning_style: str):
        """Create a crew of agents based on the subject and student parameters"""
        
        # Common support agents for both subjects
        
        age_adapter = AgeAdaptationAgent(
            openai_service=self.openai_service,
            student_age=self.student_age
        )
        
        engagement_specialist = EngagementSpecialist(
            openai_service=self.openai_service,
            student_age=self.student_age,
            learning_style=learning_style
        )
        
        progress_tracker = ProgressTracker(
            openai_service=self.openai_service,
            student_level=student_level
        )
        
        gamification_agent = GamificationAgent(
            openai_service=self.openai_service,
            student_level=student_level,
            student_age=self.student_age
        )
        
        if subject.lower() == "mathematics":
            return self._create_math_crew(
                student_age=self.student_age,
                student_level=student_level,
                learning_style=learning_style,
                age_adapter=age_adapter,
                engagement_specialist=engagement_specialist,
                progress_tracker=progress_tracker,
                gamification_agent=gamification_agent
            )
        elif subject.lower() == "english":
            return self._create_english_crew(
                student_age=self.student_age,
                student_level=student_level,
                learning_style=learning_style,
                age_adapter=age_adapter,
                engagement_specialist=engagement_specialist,
                progress_tracker=progress_tracker,
                gamification_agent=gamification_agent
            )
        else:
            raise ValueError(f"Unsupported subject: {subject}")
    
    def _create_math_crew(self, student_age, student_level, learning_style, 
                         age_adapter, engagement_specialist, progress_tracker, gamification_agent):
        """Create a crew specialized for math homework"""
        
        # Create math-specific agents
        math_analyzer = MathProblemAnalyzer(
            openai_service=self.openai_service
        )
        
        step_solver = StepByStepSolver(
            openai_service=self.openai_service,
            student_level=student_level
        )
        
        visual_explainer = VisualMathExplainer(
            openai_service=self.openai_service,
            student_age=self.student_age,
            learning_style=learning_style
        )
        
        practice_generator = MathPracticeGenerator(
            openai_service=self.openai_service,
            student_level=student_level
        )
        
        # Create agent dictionary
        agents = {
            'analyzer': math_analyzer,
            'solver': step_solver,
            'visualizer': visual_explainer,
            'practice_generator': practice_generator,
            'age_adapter': age_adapter,
            'engagement_specialist': engagement_specialist,
            'progress_tracker': progress_tracker,
            'gamification_agent': gamification_agent
        }
        
        # Create math crew with agents
        math_crew = MathCrew(agents)
        
        return math_crew
    
    def _create_english_crew(self, student_age, student_level, learning_style,
                           age_adapter, engagement_specialist, progress_tracker, gamification_agent):
        """Create a crew specialized for English homework"""
        
        # Create English-specific agents
        query_analyzer = EnglishQueryAnalyzer(openai_service=self.openai_service)
        language_specialist = LanguageArtsSpecialist(
            openai_service=self.openai_service
        )
        language_specialist = LanguageArtsSpecialist(
            openai_service=self.openai_service
        )
        
        reading_helper = ReadingComprehensionHelper(
            openai_service=self.openai_service,
            student_level=student_level
        )
        
        writing_coach = WritingCoach(
            openai_service=self.openai_service,
            student_age=self.student_age
        )
        
        practice_generator = EnglishPracticeGenerator(
            openai_service=self.openai_service,
            student_level=student_level
        )
        
        # Create agent dictionary
        agents = {
            'query_analyzer': query_analyzer,
            'language_specialist': language_specialist,
            'reading_helper': reading_helper,
            'writing_coach': writing_coach,
            'practice_generator': practice_generator,
            'age_adapter': age_adapter,
            'engagement_specialist': engagement_specialist,
            'progress_tracker': progress_tracker,
            'gamification_agent': gamification_agent
        }
        
        # Create English crew with agents
        english_crew = EnglishCrew(agents)
        
        return english_crew
