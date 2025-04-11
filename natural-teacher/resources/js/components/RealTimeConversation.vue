<template>
    <div class="conversation-container">
      <div class="conversation-messages" ref="messagesContainer">
        <div v-if="messages.length === 0" class="empty-state">
          <i class="fas fa-comment-dots"></i>
          <p>Ask any question about your homework or learning topic</p>
        </div>
        
        <div v-for="(message, index) in messages" :key="index" class="message" :class="message.sender">
          <div class="message-avatar">
            <img :src="message.sender === 'student' ? userAvatar : teacherAvatar" :alt="message.sender">
          </div>
          <div class="message-content">
            <div class="message-header">
              <span class="message-sender">{{ message.sender === 'student' ? 'You' : 'Teacher' }}</span>
              <span class="message-time">{{ formatTime(message.timestamp) }}</span>
            </div>
            
            <!-- For completed messages -->
            <div v-if="message.isComplete" v-html="formatMessage(message.content)"></div>
            
            <!-- For streaming messages -->
            <div v-else>
              <span v-html="formatMessage(message.content)"></span>
              <span class="typing-indicator">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="conversation-input">
        <div class="input-container">
          <textarea 
            ref="messageInput"
            v-model="newMessage" 
            placeholder="Ask about your homework or any topic..." 
            @keydown.enter.prevent="sendMessage"
            :disabled="inputDisabled"
            rows="1"
            @input="adjustTextareaHeight"
          ></textarea>
          
          <div class="input-buttons">
            <button @click="attachFile" class="attach-button" :disabled="inputDisabled">
              <i class="fas fa-paperclip"></i>
            </button>
            <button @click="sendMessage" class="send-button" :disabled="inputDisabled || !newMessage.trim()">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
        
        <div v-if="attachedFile" class="attachment-preview">
          <span>{{ attachedFile.name }}</span>
          <button @click="removeAttachment" class="remove-attachment">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <input 
          type="file" 
          ref="fileInput" 
          style="display: none" 
          @change="handleFileSelected" 
          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
        >
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted, onBeforeUnmount, nextTick, computed, watch } from 'vue';
  import WebSocketService from '../services/WebSocketService';
  import { useRoute } from 'vue-router';
  import markdownit from 'markdown-it';
  
  export default {
    name: 'RealTimeConversation',
    props: {
      sessionId: {
        type: String,
        default: () => `session-${Date.now()}`
      },
      userAvatar: {
        type: String,
        default: '/images/student-avatar.png'
      },
      teacherAvatar: {
        type: String,
        default: '/images/teacher-avatar.png'
      }
    },
    setup(props) {
      const route = useRoute();
      const messages = ref([]);
      const newMessage = ref('');
      const attachedFile = ref(null);
      const fileInput = ref(null);
      const messageInput = ref(null);
      const messagesContainer = ref(null);
      const currentStreamingMessage = ref(null);
      const md = new markdownit();
      
      const inputDisabled = computed(() => {
        // Disable input when we're waiting for initial response to complete
        return messages.value.some(m => m.sender === 'teacher' && !m.isComplete);
      });
      
      // Connect to WebSocket
      onMounted(() => {
        WebSocketService.connect(props.sessionId);
        WebSocketService.addHandler('message', handleMessage);
        WebSocketService.addHandler('stream', handleStreamingMessage);
        WebSocketService.addHandler('connection', handleConnectionStatus);
        
        // Start with a welcome message
        setTimeout(() => {
          messages.value.push({
            sender: 'teacher',
            content: "Hello! I'm your virtual teacher assistant. How can I help you with your homework or learning today?",
            timestamp: new Date(),
            isComplete: true
          });
        }, 500);
      });
      
      onBeforeUnmount(() => {
        WebSocketService.disconnect();
      });
      
      // Scroll to bottom when messages change
      watch(messages, () => {
        nextTick(() => {
          if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
          }
        });
      }, { deep: true });
      
      const handleMessage = (data) => {
        // Handle structured message responses (JSON)
        if (data.action === 'start_response') {
          // Start a new teacher message
          const newMessageId = Date.now().toString();
          currentStreamingMessage.value = newMessageId;
          
          messages.value.push({
            id: newMessageId,
            sender: 'teacher',
            content: '',
            timestamp: new Date(),
            isComplete: false
          });
        } else if (data.action === 'end_response') {
          // Complete the current streaming message
          const index = messages.value.findIndex(m => m.id === currentStreamingMessage.value);
          if (index !== -1) {
            messages.value[index].isComplete = true;
          }
          
          currentStreamingMessage.value = null;
        }
      };
      
      const handleStreamingMessage = (text) => {
        // Handle streaming text content
        if (currentStreamingMessage.value) {
          const index = messages.value.findIndex(m => m.id === currentStreamingMessage.value);
          if (index !== -1) {
            messages.value[index].content += text;
          }
        }
      };
      
      const handleConnectionStatus = (status) => {
        console.log('Connection status:', status);
      };
      
      const sendMessage = () => {
        if (newMessage.value.trim() === '' && !attachedFile.value) return;
        
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
          session_id: props.sessionId
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
            
            WebSocketService.sendMessage(payload);
          };
          
          reader.readAsDataURL(attachedFile.value);
        } else {
          // Send message without attachment
          WebSocketService.sendMessage(payload);
        }
        
        // Clear input
        newMessage.value = '';
        attachedFile.value = null;
        
        // Reset textarea height
        if (messageInput.value) {
          messageInput.value.style.height = 'auto';
        }
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
        }
      };
      
      const removeAttachment = () => {
        attachedFile.value = null;
        if (fileInput.value) {
          fileInput.value.value = '';
        }
      };
      
      const formatTime = (timestamp) => {
        return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      };
      
      const formatMessage = (content) => {
        // Convert markdown to HTML
        try {
          return md.render(content);
        } catch (error) {
          return content;
        }
      };
      
      const adjustTextareaHeight = () => {
        if (messageInput.value) {
          messageInput.value.style.height = 'auto';
          messageInput.value.style.height = `${messageInput.value.scrollHeight}px`;
        }
      };
      
      return {
        messages,
        newMessage,
        attachedFile,
        fileInput,
        messageInput,
        messagesContainer,
        inputDisabled,
        sendMessage,
        attachFile,
        handleFileSelected,
        removeAttachment,
        formatTime,
        formatMessage,
        adjustTextareaHeight
      };
    }
  }
  </script>
  
  <style scoped>
  .conversation-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: var(--background-color);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  .conversation-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    height: 100%;
  }
  
  .empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: var(--accent-color);
  }
  
  .message {
    display: flex;
    gap: 12px;
    max-width: 80%;
  }
  
  .message.student {
    align-self: flex-end;
    flex-direction: row-reverse;
  }
  
  .message.teacher {
    align-self: flex-start;
  }
  
  .message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
  }
  
  .message-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  .message-content {
    background-color: #e3f2fd;
    padding: 12px;
    border-radius: 12px;
    border-top-left-radius: 4px;
  }
  
  .message.student .message-content {
    background-color: #e8f5e9;
    border-top-left-radius: 12px;
    border-top-right-radius: 4px;
  }
  
  .message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
    font-size: 12px;
  }
  
  .message-sender {
    font-weight: 600;
    color: var(--primary-color);
  }
  
  .message.student .message-sender {
    color: var(--secondary-color);
  }
  
  .message-time {
    color: #666;
  }
  
  .typing-indicator {
    display: inline-flex;
    align-items: center;
    margin-left: 8px;
  }
  
  .dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    margin: 0 2px;
    background-color: #888;
    border-radius: 50%;
    opacity: 0.6;
    animation: dot-pulse 1.5s infinite ease-in-out;
  }
  
  .dot:nth-child(2) {
    animation-delay: 0.2s;
  }
  
  .dot:nth-child(3) {
    animation-delay: 0.4s;
  }
  
  @keyframes dot-pulse {
    0%, 60%, 100% {
      transform: scale(1);
      opacity: 0.6;
    }
    30% {
      transform: scale(1.5);
      opacity: 1;
    }
  }
  
  .conversation-input {
    padding: 12px;
    background-color: #fff;
    border-top: 1px solid #eaeaea;
  }
  
  .input-container {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    background-color: #f5f5f5;
    border-radius: 24px;
    padding: 8px 16px;
  }
  
  textarea {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    resize: none;
    padding: 8px 0;
    max-height: 120px;
    font-family: inherit;
    font-size: 16px;
  }
  
  .input-buttons {
    display: flex;
    gap: 8px;
  }
  
  button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s;
  }
  
  button:hover {
    background-color: rgba(0, 0, 0, 0.05);
  }
  
  button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .send-button {
    color: var(--primary-color);
  }
  
  .attachment-preview {
    margin-top: 8px;
    padding: 8px 12px;
    background-color: #f0f0f0;
    border-radius: 8px;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .remove-attachment {
    color: #666;
  }
  </style>