<template>
  <student-layout>
    <div class="homework-submit">
      <h1 class="page-title">Submit Your Homework</h1>
      
      <div class="step-container" v-if="currentStep === 3">
        <h2>Step 3</h2>
        <p>Enter your homework question:</p>
        
        <!-- Debug panel - only shown in debug mode -->
        <div v-if="debugMode" class="debug-panel">
          <h3>Connection Status</h3>
          <p>WebSocket: {{ wsStatus }}</p>
          <p>Attempts: {{ connectionAttempts }}/{{ maxConnectionAttempts }}</p>
          <div class="debug-actions">
            <button @click="testConnection" class="btn btn-secondary btn-sm">
              Test Connection
            </button>
            <button @click="toggleDebugMode" class="btn btn-secondary btn-sm">
              Hide Debug
            </button>
          </div>
        </div>
        
        <div class="input-container">
          <!-- Homework text input -->
          <div class="homework-input">
            <label for="homework-text">Type your homework question:</label>
            <textarea
              id="homework-text"
              v-model="homeworkContent"
              placeholder="Enter your homework question or problem here..."
              rows="6"
            ></textarea>
          </div>
          
          <!-- File upload option -->
          <div class="file-upload">
            <button @click="triggerFileInput" class="btn btn-outline" :disabled="isSubmitting">
              <i class="fas fa-upload"></i> Upload Image
            </button>
            
            <input 
              type="file" 
              ref="fileInput" 
              @change="handleFileUpload" 
              accept="image/*"
              style="display: none"
            >
            
            <div v-if="uploadedImage" class="image-preview">
              <img :src="uploadedImage" alt="Homework preview" class="preview-image">
              <button @click="removeImage" class="btn btn-sm btn-danger">
                <i class="fas fa-times"></i> Remove
              </button>
            </div>
          </div>
        </div>
        
        <!-- Submit button -->
        <div class="submit-controls">
          <button 
            class="btn btn-secondary" 
            @click="goToPreviousStep"
            :disabled="isSubmitting"
          >
            <i class="fas fa-arrow-left"></i> Back
          </button>
          
          <button 
            class="btn btn-primary" 
            @click="submitHomework"
            :disabled="isSubmitting || (!homeworkContent && !uploadedImage)"
          >
            <span v-if="isSubmitting">
              <i class="fas fa-spinner fa-spin"></i> Submitting...
            </span>
            <span v-else>
              <i class="fas fa-paper-plane"></i> Submit Homework
            </span>
          </button>
        </div>
        
        <!-- Conversation area (will show after submission) -->
        <div v-if="showConversation" class="conversation-area">
          <div class="messages-container" ref="messagesContainer">
            <div v-if="messages.length === 0" class="empty-messages">
              <p>Your conversation will appear here...</p>
            </div>
            
            <div 
              v-for="(message, index) in messages" 
              :key="index" 
              class="message" 
              :class="message.sender"
            >
              <div class="message-content">
                <div v-if="message.isComplete" v-html="formatMessageContent(message.content)"></div>
                <div v-else>
                  <span v-html="formatMessageContent(message.content)"></span>
                  <span class="typing-indicator">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="input-reply" v-if="messages.length > 0">
            <textarea 
              v-model="replyMessage" 
              placeholder="Ask a follow-up question..." 
              @keydown.enter.prevent="sendReply"
              :disabled="isReplying"
            ></textarea>
            <button 
              @click="sendReply" 
              :disabled="isReplying || !replyMessage.trim()"
            >
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </student-layout>
</template>

<script>
export default {
  name: 'HomeworkSubmitView',
  components: {
    StudentLayout: () => import('../../layouts/StudentLayout.vue')
  },
  data() {
    return {
      currentStep: 3,
      homeworkContent: '',
      uploadedImage: null,
      isSubmitting: false,
      showConversation: false,
      messages: [],
      replyMessage: '',
      isReplying: false,
      websocket: null,
      sessionId: `homework-${Date.now()}`,
      connectionAttempts: 0,
      maxConnectionAttempts: 5,
      wsStatus: 'Not connected',
      debugMode: true, // Set to false in production
      apiBaseUrl: 'http://127.0.0.1:5000', // Backend API URL
      file: null // Store the actual file object
    };
  },
  methods: {
    toggleDebugMode() {
      this.debugMode = !this.debugMode;
    },
    
    goToPreviousStep() {
      this.currentStep = 2; // Assuming previous step is 2
    },
    
    triggerFileInput() {
      if (this.$refs.fileInput) {
        this.$refs.fileInput.click();
      }
    },
    
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      this.file = file; // Store the file object
      
      // Create a preview URL for the uploaded image
      this.uploadedImage = URL.createObjectURL(file);
    },
    
    removeImage() {
      this.uploadedImage = null;
      this.file = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    
    formatMessageContent(content) {
      if (!content) return '';
      
      // Convert to string and escape HTML
      const escaped = String(content)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
      
      // Convert URLs to links
      const withLinks = escaped.replace(
        /(https?:\/\/[^\s]+)/g, 
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
      );
      
      // Convert newlines to <br> tags
      return withLinks.replace(/\n/g, '<br>');
    },
    
    testConnection() {
      this.wsStatus = 'Testing connection...';
      
      // Test API connection first
      fetch(`${this.apiBaseUrl}/health`)
        .then(response => {
          if (response.ok) {
            return response.json();
          }
          throw new Error(`API returned ${response.status}`);
        })
        .then(data => {
          this.wsStatus = `API available: ${JSON.stringify(data)}. Testing WebSocket...`;
          // Now test WebSocket
          this.connectWebSocket(true);
        })
        .catch(error => {
          this.wsStatus = `API error: ${error.message}`;
          console.error('API connection test failed:', error);
        });
    },
    
    connectWebSocket(isTest = false) {
      // Create a WebSocket connection to the backend
      const wsUrl = `ws://${this.apiBaseUrl.replace(/^https?:\/\//, '')}/ws/${this.sessionId}`;
      
      console.log(`Attempting to connect to WebSocket: ${wsUrl}`);
      this.wsStatus = 'Connecting...';
      
      // Close existing connection if any
      if (this.websocket) {
        this.websocket.close();
      }
      
      try {
        this.websocket = new WebSocket(wsUrl);
        
        this.websocket.onopen = (event) => {
          console.log('WebSocket connection established', event);
          this.wsStatus = 'Connected';
          this.connectionAttempts = 0;
          
          if (isTest) {
            // Send a test message
            this.websocket.send(JSON.stringify({ 
              action: 'test', 
              content: 'Testing connection'
            }));
          }
        };
        
        this.websocket.onmessage = (event) => {
          console.log('WebSocket message received:', event.data);
          
          if (isTest) {
            this.wsStatus = 'Test message received: ' + 
              (event.data.length > 30 ? 
                event.data.substring(0, 30) + '...' : 
                event.data);
            return;
          }
          
          try {
            // Try to parse as JSON for control messages
            const data = JSON.parse(event.data);
            
            if (data.action === 'start_response') {
              // Add a new teacher message that will be streamed
              this.messages.push({
                sender: 'teacher',
                content: '',
                timestamp: new Date(),
                isComplete: false
              });
              
              // Scroll to bottom
              this.scrollToBottom();
            } 
            else if (data.action === 'end_response') {
              // Mark the last message as complete
              const lastIndex = this.messages.findIndex(m => 
                m.sender === 'teacher' && !m.isComplete
              );
              
              if (lastIndex !== -1) {
                // Create a new array with the updated message to ensure reactivity
                const updatedMessages = [...this.messages];
                updatedMessages[lastIndex] = {
                  ...updatedMessages[lastIndex],
                  isComplete: true
                };
                this.messages = updatedMessages;
              }
            }
          } catch (e) {
            // Not JSON, treat as a text chunk for the current response
            const lastIndex = this.messages.findIndex(m => 
              m.sender === 'teacher' && !m.isComplete
            );
            
            if (lastIndex !== -1) {
              // Create a new array with the updated message to ensure reactivity
              const updatedMessages = [...this.messages];
              updatedMessages[lastIndex] = {
                ...updatedMessages[lastIndex],
                content: updatedMessages[lastIndex].content + event.data
              };
              this.messages = updatedMessages;
              
              // Scroll to bottom as content arrives
              this.scrollToBottom();
            } else {
              // If no incomplete message exists, create a new one
              this.messages.push({
                sender: 'teacher',
                content: event.data,
                timestamp: new Date(),
                isComplete: false
              });
              
              // Scroll to bottom
              this.scrollToBottom();
            }
          }
        };
        
        this.websocket.onclose = (event) => {
          console.log('WebSocket connection closed', event);
          this.wsStatus = `Disconnected (code: ${event.code})`;
          
          // Attempt to reconnect if this wasn't a normal closure and not a test
          if (!isTest && event.code !== 1000 && 
              this.connectionAttempts < this.maxConnectionAttempts) {
            this.connectionAttempts++;
            const reconnectDelay = 1000 * this.connectionAttempts;
            
            console.log(`Attempting to reconnect (${this.connectionAttempts}/${this.maxConnectionAttempts}) in ${reconnectDelay}ms...`);
            this.wsStatus = `Reconnecting (${this.connectionAttempts}/${this.maxConnectionAttempts}) in ${reconnectDelay}ms...`;
            
            setTimeout(() => {
              this.connectWebSocket();
            }, reconnectDelay);
          }
        };
        
        this.websocket.onerror = (error) => {
          console.error('WebSocket error:', error);
          this.wsStatus = 'Error connecting';
        };
      } catch (error) {
        console.error('Error creating WebSocket:', error);
        this.wsStatus = 'Failed to create WebSocket: ' + error.message;
      }
    },
    
    scrollToBottom() {
      this.$nextTick(() => {
        if (this.$refs.messagesContainer) {
          this.$refs.messagesContainer.scrollTop = 
            this.$refs.messagesContainer.scrollHeight;
        }
      });
    },
    
    async submitHomework() {
      if ((!this.homeworkContent && !this.uploadedImage) || this.isSubmitting) {
        return;
      }
      
      this.isSubmitting = true;
      
      try {
        // Try WebSocket first if debug mode is on
        if (this.debugMode) {
          this.connectWebSocket();
          
          // Wait for connection or timeout
          let connectionTimeoutMs = 3000; // 3 seconds timeout
          const startTime = Date.now();
          
          // Keep checking connection status until connected or timeout
          while (
            (!this.websocket || this.websocket.readyState !== WebSocket.OPEN) && 
            (Date.now() - startTime < connectionTimeoutMs)
          ) {
            // Wait a bit before checking again
            await new Promise(resolve => setTimeout(resolve, 100));
          }
          
          // If connected, submit via WebSocket
          if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            await this.submitViaWebSocket();
          } else {
            // Fallback to HTTP
            console.log('WebSocket connection failed, falling back to HTTP');
            await this.submitViaHTTP();
          }
        } else {
          // In production, just use HTTP
          await this.submitViaHTTP();
        }
        
        // Show conversation area regardless of submission method
        this.showConversation = true;
        
        // Scroll to bottom
        this.scrollToBottom();
      } catch (error) {
        console.error('Error submitting homework:', error);
        alert('There was an error submitting your homework. Please try again.');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async submitViaWebSocket() {
      return new Promise((resolve, reject) => {
        if (!this.websocket || this.websocket.readyState !== WebSocket.OPEN) {
          reject(new Error('WebSocket not connected'));
          return;
        }
        
        // Add user message to conversation
        this.messages.push({
          sender: 'user',
          content: this.homeworkContent || 'I need help with this homework.',
          timestamp: new Date(),
          isComplete: true
        });
        
        // Prepare message payload
        const payload = {
          action: 'ask',
          content: this.homeworkContent || 'I need help with this homework.',
          session_id: this.sessionId,
          student_age: 10, // Default values
          student_level: 3,
          learning_style: 'visual'
        };
        
        // If there's an image, we need to convert it to base64
        if (this.file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            try {
              // Get base64 data
              const base64Data = e.target.result.split(',')[1];
              
              // Add to payload
              payload.attachment = {
                filename: this.file.name,
                mime_type: this.file.type,
                data: base64Data
              };
              
              // Send the payload
              this.websocket.send(JSON.stringify(payload));
              resolve();
            } catch (error) {
              reject(error);
            }
          };
          
          reader.onerror = (error) => {
            reject(error);
          };
          
          reader.readAsDataURL(this.file);
        } else {
          // No file, just send the payload
          try {
            this.websocket.send(JSON.stringify(payload));
            resolve();
          } catch (error) {
            reject(error);
          }
        }
      });
    },
    
    async submitViaHTTP() {
      // Fallback to HTTP API if WebSocket is unavailable
      const apiUrl = `${this.apiBaseUrl}/real-time-conversation`;
      
      // Prepare form data for file upload
      const formData = new FormData();
      formData.append('content', this.homeworkContent || 'I need help with this homework.');
      formData.append('session_id', this.sessionId);
      formData.append('student_age', 10); // Default values
      formData.append('student_level', 3);
      formData.append('learning_style', 'visual');
      
      if (this.file) {
        formData.append('file', this.file);
      }
      
      // Add user message to conversation
      this.messages.push({
        sender: 'user',
        content: this.homeworkContent || 'I need help with this homework.',
        timestamp: new Date(),
        isComplete: true
      });
      
      try {
        // Send the HTTP request
        const response = await fetch(apiUrl, {
          method: 'POST',
          body: formData
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Add teacher response
        this.messages.push({
          sender: 'teacher',
          content: data.reply,
          timestamp: new Date(),
          isComplete: true
        });
        
        return data;
      } catch (error) {
        console.error('Error submitting homework via HTTP:', error);
        
        // Add error message to the conversation
        this.messages.push({
          sender: 'system',
          content: 'Sorry, there was an error processing your request. Please try again.',
          timestamp: new Date(),
          isComplete: true
        });
        
        throw error;
      }
    },
    
    async sendReply() {
      if (!this.replyMessage.trim() || this.isReplying) return;
      
      this.isReplying = true;
      
      try {
        // Add user reply to messages
        this.messages.push({
          sender: 'user',
          content: this.replyMessage,
          timestamp: new Date(),
          isComplete: true
        });
        
        // Store the message and clear input
        const message = this.replyMessage;
        this.replyMessage = '';
        
        // Scroll to bottom
        this.scrollToBottom();
        
        // Try WebSocket first if it's connected
        if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
          try {
            this.websocket.send(JSON.stringify({
              action: 'ask',
              content: message,
              session_id: this.sessionId,
              student_age: 10,
              student_level: 3,
              learning_style: 'visual'
            }));
          } catch (error) {
            console.error('Error sending via WebSocket:', error);
            // Fall back to HTTP
            await this.sendReplyViaHTTP(message);
          }
        } else {
          // Use HTTP as fallback
          await this.sendReplyViaHTTP(message);
        }
      } catch (error) {
        console.error('Error sending reply:', error);
        
        // Add error message
        this.messages.push({
          sender: 'system',
          content: 'Sorry, there was an error sending your message. Please try again.',
          timestamp: new Date(),
          isComplete: true
        });
        
        // Scroll to bottom
        this.scrollToBottom();
      } finally {
        this.isReplying = false;
      }
    },
    
    async sendReplyViaHTTP(message) {
      const apiUrl = `${this.apiBaseUrl}/real-time-conversation`;
      
      const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          content: message,
          session_id: this.sessionId,
          student_age: 10,
          student_level: 3,
          learning_style: 'visual'
        })
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      
      // Add teacher response
      this.messages.push({
        sender: 'teacher',
        content: data.reply,
        timestamp: new Date(),
        isComplete: true
      });
      
      // Scroll to bottom
      this.scrollToBottom();
      
      return data;
    }
  },
  mounted() {
    // For debugging
    if (this.debugMode) {
      console.log('HomeworkSubmitView mounted');
      // Make component accessible in console for debugging
      if (window) window._homeworkComponent = this;
    }
    
    // Extract API base URL from current location
    const currentHost = window.location.hostname;
    const currentPort = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
    
    // Update API URL based on environment
    if (currentHost === '127.0.0.1' || currentHost === 'localhost') {
      // Development - use port 5000
      this.apiBaseUrl = `http://${currentHost}:5000`;
    } else {
      // Production - use same host but different port if needed
      this.apiBaseUrl = `${window.location.protocol}//${currentHost}:5000`;
    }
    
    console.log(`API base URL: ${this.apiBaseUrl}`);
  },
  beforeDestroy() {
    // Close WebSocket connection when component is destroyed
    if (this.websocket) {
      this.websocket.close();
    }
    
    // Clean up any object URLs to prevent memory leaks
    if (this.uploadedImage) {
      URL.revokeObjectURL(this.uploadedImage);
    }
    
    // Remove debug reference
    if (window && window._homeworkComponent === this) {
      delete window._homeworkComponent;
    }
  }
}
</script>

<style scoped>
.homework-submit {
  padding: 20px;
}

.page-title {
  margin-bottom: 24px;
  color: #4299e1;
}

.step-container {
  background-color: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.input-container {
  margin: 24px 0;
}

.homework-input {
  margin-bottom: 16px;
}

.homework-input label {
  display: block;
  margin-bottom: 8px;
  font-weight: bold;
}

textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-family: inherit;
  resize: vertical;
}

.file-upload {
  margin-top: 16px;
}

.btn {
  padding: 8px 16px;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-outline {
  border: 1px solid #4299e1;
  color: #4299e1;
  background: transparent;
}

.btn-outline:hover {
  background-color: rgba(66, 153, 225, 0.1);
}

.btn-primary {
  background-color: #4299e1;
  color: white;
}

.btn-primary:hover {
  background-color: #3182ce;
}

.btn-secondary {
  background-color: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background-color: #cbd5e0;
}

.btn-danger {
  background-color: #e53e3e;
  color: white;
}

.btn-danger:hover {
  background-color: #c53030;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 0.875rem;
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.image-preview {
  margin-top: 16px;
  text-align: center;
}

.preview-image {
  max-width: 100%;
  max-height: 300px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 8px;
}

.submit-controls {
  display: flex;
  justify-content: space-between;
  margin-top: 24px;
}

/* Debug panel */
.debug-panel {
  margin-bottom: 16px;
  padding: 12px;
  background-color: #fffde7;
  border: 1px solid #fff59d;
  border-radius: 4px;
}

.debug-panel h3 {
  margin-top: 0;
  margin-bottom: 8px;
  color: #f57f17;
}

.debug-panel p {
  margin: 4px 0;
  font-family: monospace;
}

.debug-actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

/* Conversation styles */
.conversation-area {
  margin-top: 32px;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

.messages-container {
  height: 300px;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  background-color: #f9f9f9;
}

.empty-messages {
  text-align: center;
  color: #666;
  margin: auto 0;
  font-style: italic;
}

.message {
  max-width: 80%;
  padding: 12px;
  border-radius: 8px;
}

.message.user {
  align-self: flex-end;
  background-color: #e9f5fe;
  border-bottom-right-radius: 4px;
}

.message.teacher {
  align-self: flex-start;
  background-color: #f0f0f0;
  border-bottom-left-radius: 4px;
}

.message.system {
  align-self: center;
  background-color: #fff3cd;
  color: #856404;
  font-style: italic;
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

.input-reply {
  display: flex;
  gap: 8px;
  padding: 12px;
  background-color: white;
  border-top: 1px solid #ddd;
}

.input-reply textarea {
  flex: 1;
  max-height: 100px;
  min-height: 40px;
}

.input-reply button {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #4299e1;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
}

.input-reply button:hover {
  background-color: #3182ce;
}

.input-reply button:disabled {
  background-color: #cbd5e0;
  cursor: not-allowed;
}
</style>