class ConversationManager:
    def __init__(self, session_id, student_info=None):
        self.session_id = session_id
        self.student_info = student_info or {}
        self.conversation_history = []
        self.learning_context = {}
        self.active_topics = set()
        
    def add_message(self, message, sender_type):
        """Add a message to the conversation history with timestamp"""
        self.conversation_history.append({
            "content": message,
            "sender": sender_type,  # "student" or "teacher"
            "timestamp": datetime.now()
        })
        
    def get_context_for_response(self):
        """Prepare the relevant context for generating a teacher response"""
        return {
            "history": self.conversation_history[-10:],  # Recent context
            "student_info": self.student_info,
            "learning_context": self.learning_context,
            "active_topics": list(self.active_topics)
        }