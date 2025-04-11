<template>
  <student-layout>
    <div class="homework-submit">
      <h1 class="page-title">Submit Your Homework</h1>
      
      <div class="homework-workflow" v-if="currentStep === 1">
        <div class="step-container">
          <h2>Step one 1</h2>
          <p>What subject are you working on?</p>
          
          <div class="subject-selector">
            <div class="subjects-grid">
              <div
                v-for="subject in subjects"
                :key="subject.id"
                class="subject-card"
                :class="{ 'selected': selectedSubject?.id === subject.id }"
                @click="selectSubject(subject)"
              >
                <i :class="subject.icon"></i>
                <h3>{{ subject.name }}</h3>
                <p>{{ subject.description }}</p>
              </div>
            </div>
          </div>
          
          <div class="navigation-buttons">
            <button class="btn btn-secondary" disabled>
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="btn btn-primary" @click="goToStep(2)" :disabled="!selectedSubject">
              Next <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
      
      <div class="homework-workflow" v-if="currentStep === 2">
        <div class="step-container">
          <h2>Step 2</h2>
          <p>Select a topic:</p>
          
          <div class="topics-grid">
            <div
              v-for="topic in filteredTopics"
              :key="topic.id"
              class="topic-card"
              :class="{ 'selected': selectedTopic?.id === topic.id }"
              @click="selectTopic(topic)"
            >
              <h3>{{ topic.name }}</h3>
              <p>{{ topic.description }}</p>
              <div class="topic-footer">
                <span>Difficulty: {{ topic.difficulty }}</span>
                <span>{{ topic.points_available }} points</span>
              </div>
            </div>
          </div>
          
          <div class="navigation-buttons">
            <button class="btn btn-secondary" @click="goToStep(1)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="btn btn-primary" @click="goToStep(3)" :disabled="!selectedTopic">
              Next <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
      
      <div class="homework-workflow" v-if="currentStep === 3">
        <div class="step-container full-height">
          <h2>Step 3</h2>
          <p>Enter your homework question:</p>
          
          <!-- Real-time conversation component -->
          <real-time-conversation 
            :sessionId="conversationSessionId"
            class="conversation-container"
          />
          
          <div class="navigation-buttons">
            <button class="btn btn-secondary" @click="goToStep(2)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
          </div>
        </div>
      </div>
    </div>
  </student-layout>
</template>
<script>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import StudentLayout from '../../layouts/StudentLayout.vue';
import axios from 'axios';
import RealTimeConversation from '../../components/RealTimeConversation.vue';

export default {
  name: 'HomeworkSubmitView',
  components: {
    StudentLayout,
    RealTimeConversation
  },
  setup() {
    // State
    const currentStep = ref(1);
    const showWorkflow = ref(true);
    const selectedSubject = ref(null);
    const selectedTopic = ref(null);
    const conversationSessionId = ref('');
    const websocket = ref(null);
    const isTeacherConnected = ref(false);
    const messages = ref([]);
    const newMessage = ref('');
    const attachedFile = ref(null);
    const attachmentPreview = ref(null);
    const fileInput = ref(null);
    const messageInput = ref(null);
    const messagesContainer = ref(null);
    const isMessageSending = ref(false);
    const currentStreamingMessage = ref(null);
    
    // Get subjects and topics from API
    const subjects = ref([]);
    const topics = ref([]);
    
    const filteredTopics = computed(() => {
      if (!selectedSubject.value) return [];
      return topics.value.filter(topic => topic.subject_id === selectedSubject.value.id);
    });
    
    // Fetch subjects and topics
    const fetchSubjects = async () => {
      try {
        const response = await axios.get('/api/subjects');
        subjects.value = response.data;
      } catch (error) {
        console.error('Failed to fetch subjects:', error);
      }
    };
    
    const fetchTopics = async () => {
      try {
        const response = await axios.get('/api/topics');
        topics.value = response.data;
      } catch (error) {
        console.error('Failed to fetch topics:', error);
      }
    };
    
    // WebSocket connection
    const connectWebSocket = (sessionId) => {
      // Close existing connection if any
      if (websocket.value) {
        websocket.value.close();
      }
      
      // Determine the WebSocket URL
      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
      // const host = window.location.host;
      const host = `${window.location.hostname}:5000`; 
      const wsUrl = `${protocol}//${host}/ws/${sessionId}`;
      
      // Create new WebSocket connection
      websocket.value = new WebSocket(wsUrl);
      
      // Set up event handlers
      websocket.value.onopen = handleSocketOpen;
      websocket.value.onmessage = handleSocketMessage;
      websocket.value.onclose = handleSocketClose;
      websocket.value.onerror = handleSocketError;
    };
    
    const handleSocketOpen = (event) => {
      console.log('WebSocket connection established');
      isTeacherConnected.value = true;
      
      // Send session info
      if (selectedSubject.value && selectedTopic.value) {
        sendSessionInfo();
      }
    };
    
    const handleSocketMessage = (event) => {
      try {
        // First try to parse as JSON (for control messages)
        const data = JSON.parse(event.data);
        
        if (data.action === 'start_response') {
          // Start a new teacher message
          const newMessageId = Date.now().toString();
          currentStreamingMessage.value = newMessageId;
          
          messages.value.push({
            id: newMessageId,
            sender: 'teacher',
            content: '',
            timestamp: new Date(),
            isComplete: false,
            feedbackGiven: false
          });
          
          // Scroll to the bottom
          nextTick(() => {
            scrollToBottom();
          });
        } else if (data.action === 'end_response') {
          // Complete the current streaming message
          const index = messages.value.findIndex(m => m.id === currentStreamingMessage.value);
          if (index !== -1) {
            messages.value[index].isComplete = true;
          }
          
          currentStreamingMessage.value = null;
          isMessageSending.value = false;
        } else if (data.action === 'connection_established') {
          isTeacherConnected.value = true;
        }
      } catch (e) {
        // Not JSON, treat as streaming text content
        if (currentStreamingMessage.value) {
          const index = messages.value.findIndex(m => m.id === currentStreamingMessage.value);
          if (index !== -1) {
            messages.value[index].content += event.data;
            
            // Scroll to the bottom as new content arrives
            nextTick(() => {
              scrollToBottom();
            });
          }
        }
      }
    };
    
    const handleSocketClose = (event) => {
      console.log('WebSocket connection closed:', event);
      isTeacherConnected.value = false;
      
      // Attempt to reconnect after a delay if not an intentional close
      if (event.code !== 1000) {
        setTimeout(() => {
          if (conversationSessionId.value) {
            connectWebSocket(conversationSessionId.value);
          }
        }, 3000);
      }
    };
    
    const handleSocketError = (error) => {
      console.error('WebSocket error:', error);
    };
    
    const sendSessionInfo = () => {
      if (!websocket.value || websocket.value.readyState !== WebSocket.OPEN) return;
      
      const sessionInfo = {
        action: 'set_session_info',
        user_info: {
          subject_info: {
            subject: selectedSubject.value.name,
            subject_id: selectedSubject.value.id
          },
          topic_info: {
            topic: selectedTopic.value.name,
            topic_id: selectedTopic.value.id
          }
        }
      };
      
      websocket.value.send(JSON.stringify(sessionInfo));
    };
    
    const sendMessage = () => {
      if (isMessageSending.value || (!newMessage.value.trim() && !attachedFile.value)) return;
      
      isMessageSending.value = true;
      
      // Add user message to the chat
      messages.value.push({
        id: Date.now().toString(),
        sender: 'student',
        content: newMessage.value,
        timestamp: new Date(),
        isComplete: true
      });
      
      // Create message payload
      const payload = {
        action: 'ask',
        content: newMessage.value,
        session_id: conversationSessionId.value
      };
      
      // Add file if attached
      if (attachedFile.value) {
        // Convert file to base64 string for websocket transmission
        const reader = new FileReader();
        reader.onload = (e) => {
          payload.attachment = {
            filename: attachedFile.value.name,
            mime_type: attachedFile.value.type,
            data: e.target.result.split(',')[1] // Remove data URL prefix
          };
          
          sendWebSocketMessage(payload);
        };
        
        reader.readAsDataURL(attachedFile.value);
      } else {
        // Send message without attachment
        sendWebSocketMessage(payload);
      }
      
      // Clear input
      newMessage.value = '';
      attachedFile.value = null;
      attachmentPreview.value = null;
      
      // Reset textarea height
      if (messageInput.value) {
        messageInput.value.style.height = 'auto';
      }
      
      // Scroll to bottom
      nextTick(() => {
        scrollToBottom();
      });
    };
    
    const sendWebSocketMessage = (payload) => {
      if (!websocket.value || websocket.value.readyState !== WebSocket.OPEN) {
        // Reconnect and queue the message
        connectWebSocket(conversationSessionId.value);
        setTimeout(() => {
          sendWebSocketMessage(payload);
        }, 1000);
        return;
      }
      
      websocket.value.send(JSON.stringify(payload));
    };
    
    const selectSubject = (subject) => {
      selectedSubject.value = subject;
      selectedTopic.value = null; // Reset topic when subject changes
    };
    
    const selectTopic = (topic) => {
      selectedTopic.value = topic;
    };
    
    const goToStep = (step) => {
      currentStep.value = step;
      
      // If going to the conversation step, initialize the session
      if (step === 3) {
        // Generate a session ID that includes subject and topic info
        conversationSessionId.value = `homework-${selectedSubject.value.id}-${selectedTopic.value.id}-${Date.now()}`;
        
        // Connect to WebSocket
        connectWebSocket(conversationSessionId.value);
      }
    };
    
    const toggleWorkflow = () => {
      showWorkflow.value = !showWorkflow.value;
      if (showWorkflow.value) {
        currentStep.value = 2; // Go back to topic selection
      }
    };
    
    const refreshSession = () => {
      // Clear messages and start a new session
      messages.value = [];
      
      // Generate a new session ID
      conversationSessionId.value = `homework-${selectedSubject.value.id}-${selectedTopic.value.id}-${Date.now()}`;
      
      // Reconnect WebSocket
      connectWebSocket(conversationSessionId.value);
    };
    
    const attachFile = () => {
      if (fileInput.value) {
        fileInput.value.click();
      }
    };
    
    const handleFileSelected = (event) => {
      const file = event.target.files[0];
      if (file) {
        attachedFile.value = file;
        
        // Create preview if it's an image
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            attachmentPreview.value = e.target.result;
          };
          reader.readAsDataURL(file);
        } else {
          attachmentPreview.value = null;
        }
      }
    };
    
    const removeAttachment = () => {
      attachedFile.value = null;
      attachmentPreview.value = null;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    };
    
    const formatTime = (timestamp) => {
      return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };
    
    const isImageAttachment = computed(() => {
      return attachedFile.value && attachedFile.value.type.startsWith('image/');
    });
    
    const formatMessage = (content) => {
      // Basic formatting like newlines to <br> and links
      if (!content) return '';
      
      // Replace URLs with links
      const urlRegex = /(https?:\/\/[^\s]+)/g;
      const withLinks = content.replace(urlRegex, '<a href="$1" target="_blank">$1</a>');
      
      // Replace newlines with <br>
      return withLinks.replace(/\n/g, '<br>');
    };
    
    const scrollToBottom = () => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      }
    };
    
    const adjustTextareaHeight = () => {
      if (messageInput.value) {
        messageInput.value.style.height = 'auto';
        messageInput.value.style.height = `${messageInput.value.scrollHeight}px`;
      }
    };
    
    const provideMessageFeedback = (messageIndex, isHelpful) => {
      const message = messages.value[messageIndex];
      if (!message || message.sender !== 'teacher' || message.feedbackGiven) return;
      
      // Mark feedback as given
      message.feedbackGiven = true;
      
      // Send feedback to server
      if (websocket.value && websocket.value.readyState === WebSocket.OPEN) {
        websocket.value.send(JSON.stringify({
          action: 'feedback',
          feedback: {
            message_id: message.id,
            is_helpful: isHelpful
          }
        }));
      }
    };
    
    // Fetch data on mount
    onMounted(() => {
      fetchSubjects();
      fetchTopics();
    });
    
    // Clean up WebSocket connection when component unmounts
    onBeforeUnmount(() => {
      if (websocket.value) {
        websocket.value.close();
      }
    });
    
    // Watch for messages changes to scroll to bottom
    watch(messages, () => {
      nextTick(() => {
        scrollToBottom();
      });
    }, { deep: true });
    
    return {
      // State
      currentStep,
      showWorkflow,
      subjects,
      selectedSubject,
      topics,
      filteredTopics,
      selectedTopic,
      conversationSessionId,
      isTeacherConnected,
      messages,
      newMessage,
      attachedFile,
      attachmentPreview,
      fileInput,
      messageInput,
      messagesContainer,
      isMessageSending,
      isImageAttachment,
      
      // Methods
      selectSubject,
      selectTopic,
      goToStep,
      toggleWorkflow,
      refreshSession,
      sendMessage,
      attachFile,
      handleFileSelected,
      removeAttachment,
      formatTime,
      formatMessage,
      adjustTextareaHeight,
      provideMessageFeedback,
      
      // Constants
      stepLabels: ['Subject', 'Topic', 'Conversation']
    };
  }
}
</script>
<style scoped>
.homework-submit {
  padding: 20px;
}

.page-title {
  margin-bottom: 20px;
  color: var(--primary-color);
}

.step-container {
  background-color: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.full-height {
  height: calc(80vh - 150px);
  display: flex;
  flex-direction: column;
}

.navigation-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.subjects-grid, .topics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 16px;
  margin: 20px 0;
}

.subject-card, .topic-card {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.subject-card:hover, .topic-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.subject-card.selected, .topic-card.selected {
  border-color: var(--primary-color);
  background-color: rgba(var(--primary-color-rgb), 0.1);
}

.subject-card i {
  font-size: 32px;
  margin-bottom: 12px;
  color: var(--primary-color);
}

.topic-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 12px;
  font-size: 14px;
  color: #666;
}

.conversation-container {
  flex: 1;
  margin: 20px 0;
  min-height: 500px;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}
</style>