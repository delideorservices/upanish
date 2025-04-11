<template>
    <div class="conversation-container">
      <div class="conversation-messages" ref="messagesContainer">
        <div v-if="messages.length === 0" class="empty-state">
          <i class="fas fa-comment-dots"></i>
          <p>Ask any question about your homework or learning topic</p>
        </div>
  
        <div
          v-for="(message, index) in messages"
          :key="index"
          class="message"
          :class="message.sender"
        >
          <div class="message-avatar">
            <img
              :src="message.sender === 'student' ? userAvatar : teacherAvatar"
              :alt="message.sender"
            />
          </div>
          <div class="message-content">
            <div class="message-header">
              <span class="message-sender">{{ message.sender === 'student' ? 'You' : 'Teacher' }}</span>
              <span class="message-time">{{ formatTime(message.timestamp) }}</span>
            </div>
            <div v-if="message.isComplete" v-html="formatMessage(message.content)"></div>
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
            placeholder="Ask your homework question here..."
            @keydown.enter.prevent="sendMessage"
            :disabled="inputDisabled"
            rows="1"
            @input="adjustTextareaHeight"
          ></textarea>
  
          <div class="input-buttons">
            <button @click="attachFile" class="attach-button" :disabled="inputDisabled">
              <i class="fas fa-paperclip"></i>
            </button>
            <button
              @click="sendMessage"
              class="send-button"
              :disabled="inputDisabled || !newMessage.trim()"
            >
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
        />
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, onBeforeUnmount, nextTick, computed, watch } from 'vue';
  import WebSocketService from '../services/WebSocketService';
  import markdownit from 'markdown-it';
  
  const props = defineProps({
    sessionId: String,
    userAvatar: { type: String, default: 'https://i.imgur.com/LxFvZ1R.png' },
    teacherAvatar: { type: String, default: 'https://i.imgur.com/M8bPjmc.png' }
  });
  
  const messages = ref([]);
  const newMessage = ref('');
  const attachedFile = ref(null);
  const fileInput = ref(null);
  const messageInput = ref(null);
  const messagesContainer = ref(null);
  const currentStreamingMessage = ref(null);
  const md = new markdownit();
  
  const inputDisabled = computed(() => {
    return messages.value.some(m => m.sender === 'teacher' && !m.isComplete);
  });
  
  onMounted(() => {
    WebSocketService.connect(props.sessionId);
    WebSocketService.addHandler('message', handleMessage);
    WebSocketService.addHandler('stream', handleStreamingMessage);
    WebSocketService.addHandler('connection', status => console.log('Connection:', status));
  
    setTimeout(() => {
      messages.value.push({
        sender: 'teacher',
        content: "Hello! I'm your virtual teacher assistant. How can I help you today?",
        timestamp: new Date(),
        isComplete: true
      });
    }, 500);
  });
  
  onBeforeUnmount(() => {
    WebSocketService.disconnect();
  });
  
  watch(messages, () => {
    nextTick(() => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
      }
    });
  }, { deep: true });
  
  const handleMessage = (data) => {
    if (data.action === 'start_response') {
      const id = Date.now().toString();
      currentStreamingMessage.value = id;
      messages.value.push({ id, sender: 'teacher', content: '', timestamp: new Date(), isComplete: false });
    } else if (data.action === 'end_response') {
      const index = messages.value.findIndex(m => m.id === currentStreamingMessage.value);
      if (index !== -1) messages.value[index].isComplete = true;
      currentStreamingMessage.value = null;
    }
  };
  
  const handleStreamingMessage = (text) => {
    if (currentStreamingMessage.value) {
      const index = messages.value.findIndex(m => m.id === currentStreamingMessage.value);
      if (index !== -1) messages.value[index].content += text;
    }
  };
  
  const sendMessage = () => {
    if (!newMessage.value.trim() && !attachedFile.value) return;
  
    messages.value.push({
      id: Date.now().toString(),
      sender: 'student',
      content: newMessage.value,
      timestamp: new Date(),
      isComplete: true
    });
  
    const payload = {
      action: 'ask',
      content: newMessage.value,
      session_id: props.sessionId
    };
  
    if (attachedFile.value) {
      const reader = new FileReader();
      reader.onload = (e) => {
        payload.attachment = {
          filename: attachedFile.value.name,
          mime_type: attachedFile.value.type,
          data: e.target.result.split(',')[1]
        };
        WebSocketService.sendMessage(payload);
      };
      reader.readAsDataURL(attachedFile.value);
    } else {
      WebSocketService.sendMessage(payload);
    }
  
    newMessage.value = '';
    attachedFile.value = null;
    if (messageInput.value) messageInput.value.style.height = 'auto';
  };
  
  const attachFile = () => fileInput.value && fileInput.value.click();
  const handleFileSelected = (e) => attachedFile.value = e.target.files[0];
  const removeAttachment = () => {
    attachedFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
  };
  const formatTime = (ts) => new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const formatMessage = (content) => md.render(content || '');
  const adjustTextareaHeight = () => {
    if (messageInput.value) {
      messageInput.value.style.height = 'auto';
      messageInput.value.style.height = `${messageInput.value.scrollHeight}px`;
    }
  };
  </script>
  
  <style scoped>
  .conversation-container {
    height: 600px;
    display: flex;
    flex-direction: column;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: #fff;
  }
  
  .conversation-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    gap: 16px;
    display: flex;
    flex-direction: column;
  }
  
  .conversation-input {
    padding: 12px;
    border-top: 1px solid #eee;
    background-color: #f9f9f9;
  }
  
  .input-container {
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 20px;
    background: #fff;
    padding: 10px 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  
  textarea {
    flex: 1;
    resize: none;
    border: none;
    outline: none;
    padding: 8px;
    font-size: 15px;
    border-radius: 8px;
  }
  
  .input-buttons button {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #444;
  }
  
  .input-buttons button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  </style>
  