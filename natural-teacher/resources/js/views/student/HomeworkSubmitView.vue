<template>
    <student-layout>
      <div class="homework-submit">
        <h1 class="page-title">Submit Your Homework</h1>
        
        <div class="subject-selection" v-if="step === 1">
          <h2>What subject are you working on?</h2>
          
          <div class="subjects-grid">
            <div 
              v-for="subject in subjects" 
              :key="subject.id"
              class="subject-card"
              :class="{ 'selected': selectedSubject?.id === subject.id }"
              @click="selectSubject(subject)"
            >
              <div class="subject-icon">
                <i :class="fa-camera"></i>
              </div>
              <h3>{{ subject.name }}</h3>
              <p>{{ subject.description }}</p>
            </div>
          </div>
          
          <div class="navigation-buttons">
            <button 
              class="btn btn-primary" 
              :disabled="!selectedSubject" 
              @click="goToStep(2)"
            >
              Next <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>
        
        <div class="topic-selection" v-if="step === 2">
          <h2>Select a topic</h2>
          
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
                <div class="difficulty">
                  <span>Difficulty:</span>
                  <div class="stars">
                    <i 
                      v-for="n in 5" 
                      :key="n"
                      class="fas fa-star"
                      :class="{ 'filled': n <= topic.difficulty_level }"
                    ></i>
                  </div>
                </div>
                <div class="points">
                  <i class="fas fa-star"></i>
                  {{ topic.points_available }} points
                </div>
              </div>
            </div>
          </div>
          
          <div class="navigation-buttons">
            <button class="btn btn-secondary" @click="goToStep(1)">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            
            <button 
            class="btn btn-primary" 
            :disabled="!selectedTopic" 
            @click="goToStep(3)"
          >
            Next <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
      
      <div class="homework-input" v-if="step === 3">
        <h2>Enter your homework question</h2>
        
        <div class="input-methods">
          <div class="input-method" :class="{ 'active': inputMethod === 'text' }" @click="inputMethod = 'text'">
            <i class="fas fa-keyboard"></i>
            <span>Type Question</span>
          </div>
          
          <div class="input-method" :class="{ 'active': inputMethod === 'photo' }" @click="inputMethod = 'photo'">
            <i class="fas fa-camera"></i>
            <span>Upload Photo</span>
          </div>
        </div>
        
        <div class="input-container">
          <div v-if="inputMethod === 'text'">
            <label for="homework-text">Type your homework question:</label>
            <textarea 
              id="homework-text" 
              v-model="homeworkContent" 
              rows="5" 
              placeholder="Enter your homework question or problem here..."
            ></textarea>
          </div>
          
          <div v-else-if="inputMethod === 'photo'" class="photo-upload">
            <label for="homework-photo">
              <div class="upload-area" :class="{ 'has-file': homeworkFile }">
                <div v-if="!homeworkFile">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Drag & drop your homework photo or click to browse</p>
                </div>
                <div v-else class="file-preview">
                  <img :src="filePreview" alt="Homework preview" />
                </div>
              </div>
            </label>
            <input 
              type="file" 
              id="homework-photo" 
              ref="fileInput"
              accept="image/*" 
              @change="handleFileUpload" 
              style="display: none;"
            />
            
            <button v-if="homeworkFile" class="btn btn-outline" @click="clearFile">
              <i class="fas fa-times"></i> Remove Photo
            </button>
          </div>
        </div>
        
        <div class="navigation-buttons">
          <button class="btn btn-secondary" @click="goToStep(2)">
            <i class="fas fa-arrow-left"></i> Back
          </button>
          
          <button 
            class="btn btn-primary" 
            :disabled="!isValidHomework" 
            @click="submitHomework"
            :class="{ 'loading': submitting }"
          >
            <span v-if="!submitting">Submit Homework</span>
            <span v-else>
              <i class="fas fa-spinner fa-spin"></i> Processing...
            </span>
          </button>
        </div>
      </div>
      
      <div class="homework-result" v-if="step === 4">
        <div class="result-header">
          <h2>Your Homework Help</h2>
          <div class="points-earned">
            <i class="fas fa-star animate__animated animate__tada"></i>
            <span>+{{ pointsEarned }} points earned!</span>
          </div>
        </div>

        <div class="result-card">
          <div class="question-section">
            <h3>Your Question:</h3>
            <div class="question-content">
              <p v-if="homeworkContent">{{ homeworkContent }}</p>
              <img v-else-if="filePreview" :src="filePreview" alt="Homework question" />
            </div>
          </div>

          <div class="answer-section">
            <h3>Solution:</h3>
            <transition name="fade">
              <div class="chat-box">
                <div 
                  v-for="(msg, index) in homeworkResponse.messages" 
                  :key="index"
                  :class="['message', msg.sender === 'student' ? 'student' : 'agent']"
                >
                  <span class="label">{{ msg.sender === 'student' ? 'You' : 'Teacher' }}</span>
                  <div class="bubble">{{ msg.message }}</div>
                </div>
              </div>

            </transition>
          </div>

          <div class="feedback-section">
            <h3>Was this helpful?</h3>
            <div class="feedback-buttons">
              <button class="btn btn-outline" @click="provideFeedback('very_helpful')">
                <i class="far fa-grin-stars"></i> Very Helpful
              </button>
              <button class="btn btn-outline" @click="provideFeedback('somewhat_helpful')">
                <i class="far fa-smile"></i> Somewhat
              </button>
              <button class="btn btn-outline" @click="provideFeedback('not_helpful')">
                <i class="far fa-frown"></i> Not Helpful
              </button>
            </div>
          </div>
        </div>

        <div class="navigation-buttons">
          <button class="btn btn-primary" @click="startNew">
            <i class="fas fa-plus"></i> New Homework Question
          </button>
        </div>
      </div>
    </div>
  </student-layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import MarkdownIt from 'markdown-it';
import { useGamificationStore } from '../../stores/gamification';

const gamification = useGamificationStore();
const md = new MarkdownIt({ html: true, breaks: true, linkify: true });

const step = ref(1);
const subjects = ref([]);
const topics = ref([]);
const selectedSubject = ref(null);
const selectedTopic = ref(null);
const inputMethod = ref('text');
const homeworkContent = ref('');
const homeworkFile = ref(null);
const filePreview = ref(null);
const submitting = ref(false);
const homeworkResponse = ref({});
const pointsEarned = ref(0);

const renderedMarkdown = computed(() => md.render(homeworkResponse.value.content || ''));
const typedMarkdown = ref('');
const sessionId = ref(null);
const studentAge = ref(null);
const studentLevel = ref(null);
const learningStyle = ref(null);




watch(renderedMarkdown, async (newVal) => {
  typedMarkdown.value = '';
  for (let i = 0; i < newVal.length; i++) {
    typedMarkdown.value += newVal[i];
    await new Promise(resolve => setTimeout(resolve, 1));
  }
});

onMounted(async () => {
  try {
    const [subjectsResponse, topicsResponse] = await Promise.all([
      axios.get('/api/subjects'),
      axios.get('/api/topics')
    ]);
    subjects.value = subjectsResponse.data;
    topics.value = topicsResponse.data;
  } catch (error) {
    console.error('Failed to load subjects and topics:', error);
  }
});

const filteredTopics = computed(() => {
  if (!selectedSubject.value) return [];
  return topics.value.filter(topic => topic.subject_id === selectedSubject.value.id && topic.is_active);
});

const isValidHomework = computed(() => {
  return inputMethod.value === 'text' ? homeworkContent.value.trim().length > 0 : homeworkFile.value !== null;
});

const selectSubject = (subject) => selectedSubject.value = subject;
const selectTopic = (topic) => selectedTopic.value = topic;
const goToStep = (newStep) => step.value = newStep;

const fileInputRef = ref(null);
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  homeworkFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => filePreview.value = e.target.result;
  reader.readAsDataURL(file);
};
const clearFile = () => {
  homeworkFile.value = null;
  filePreview.value = null;
  if (fileInputRef.value) fileInputRef.value.value = null;
};

// const submitHomework = async () => {
//   submitting.value = true;
//   try {
//     const formData = new FormData();
//     formData.append('subject_id', selectedSubject.value.id);
//     formData.append('topic_id', selectedTopic.value.id);
//     formData.append('input_type', inputMethod.value);
//     if (inputMethod.value === 'text') {
//       formData.append('content', homeworkContent.value);
//     } else {
//       formData.append('file', homeworkFile.value);
//     }
//     const response = await axios.post('/api/homework/submit', formData, {
//       headers: { 'Content-Type': 'multipart/form-data' }
//     });
//     // homeworkResponse.value = response.data.response;
//     homeworkResponse.value = {
//       id: response.data.response.id,
//       content: response.data.response.content || response.data.response.raw || 'No answer found.'
//     };

//     pointsEarned.value = response.data.points_earned || selectedTopic.value.points_available;
//     gamification.addPoints(pointsEarned.value);
//     await gamification.checkForNewAchievements();
//     step.value = 4;
//   } catch (error) {
//     console.error('Failed to submit homework:', error);
//     alert('Sorry, there was a problem submitting your homework. Please try again.');
//   } finally {
//     submitting.value = false;
//   }
// };
const submitHomework = async () => {
  submitting.value = true;
  try {
    // 1. Create session by calling /submit
    const formData = new FormData();
    formData.append('subject_id', selectedSubject.value.id);
    formData.append('topic_id', selectedTopic.value.id);
    formData.append('input_type', 'text');
    formData.append('content', homeworkContent.value);

    const sessionResponse = await axios.post('/api/homework/submit', formData);

    sessionId.value = sessionResponse.data.session_id;
    studentAge.value = sessionResponse.data.student_age;
    studentLevel.value = sessionResponse.data.student_level;
    learningStyle.value = sessionResponse.data.learning_style;

    // 2. Now call real-time conversation
    const realTimeResponse = await axios.post('/api/homework/real-time-conversation', {
      message: homeworkContent.value,
      subject_id: selectedSubject.value.id,
      session_id: sessionId.value,
      student_age: studentAge.value,
      student_level: studentLevel.value,
      learning_style: learningStyle.value,
    });

    const data = realTimeResponse.data.response;
    homeworkResponse.value = {
      id: data.id || null,
      messages: [
        { sender: 'student', message: homeworkContent.value },
        { sender: 'agent', message: data.content || 'No answer found.' },
      ]
    };

    pointsEarned.value = sessionResponse.data.points_earned || selectedTopic.value.points_available;
    step.value = 4;

  } catch (error) {
    console.error('❌ Failed to submit real-time homework:', error);
    alert('Oops! Something went wrong. Try again.');
  } finally {
    submitting.value = false;
  }
};


const provideFeedback = async (feedbackType) => {
  try {
    await axios.post('/api/homework/feedback', {
      response_id: homeworkResponse.value.id,
      feedback_type: feedbackType
    });
    alert('Thank you for your feedback!');
  } catch (error) {
    console.error('Failed to submit feedback:', error);
  }
};

const startNew = () => {
  selectedSubject.value = null;
  selectedTopic.value = null;
  inputMethod.value = 'text';
  homeworkContent.value = '';
  homeworkFile.value = null;
  filePreview.value = null;
  homeworkResponse.value = {};
  step.value = 1;
};
</script>


<style scoped>
.homework-submit {
  max-width: 900px;
  margin: 0 auto;
}

.page-title {
  font-size: 2rem;
  color: var(--primary-color);
  margin-bottom: 2rem;
  text-align: center;
}

.subjects-grid, .topics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
  margin: 2rem 0;
}

.subject-card, .topic-card {
  background-color: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.subject-card:hover, .topic-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.subject-card.selected, .topic-card.selected {
  border-color: var(--primary-color);
  background-color: rgba(var(--primary-color-rgb), 0.05);
}

.subject-icon {
  font-size: 2.5rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
  text-align: center;
}

.navigation-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
}

.input-methods {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.input-method {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
  flex: 1;
  background-color: #f5f5f5;
  transition: all 0.3s ease;
}

.input-method i {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.input-method.active {
  background-color: var(--primary-color);
  color: white;
}

.input-container {
  margin-bottom: 2rem;
}

textarea {
  width: 100%;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: var(--border-radius);
  font-family: var(--font-family);
  resize: vertical;
}

.photo-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.upload-area {
  width: 100%;
  height: 200px;
  border: 2px dashed #ddd;
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: all 0.3s ease;
}

.upload-area:hover {
  border-color: var(--primary-color);
}

.upload-area i {
  font-size: 3rem;
  color: #aaa;
  margin-bottom: 1rem;
}

.file-preview {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.file-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.points-earned {
  background-color: var(--accent-color);
  color: #333;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.result-card {
  background-color: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 2rem;
  margin-bottom: 2rem;
}

.question-section, .answer-section {
  margin-bottom: 2rem;
}

.question-content, .answer-content {
  padding: 1rem;
  background-color: #f9f9f9;
  border-radius: var(--border-radius);
}

.feedback-buttons {
  display: flex;
  gap: 1rem;
}

.stars {
  display: flex;
  gap: 0.25rem;
}

.stars i {
  color: #ddd;
}

.stars i.filled {
  color: var(--accent-color);
}

.topic-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.btn-outline {
  background-color: transparent;
  border: 1px solid currentColor;
  color: var(--primary-color);
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-outline:hover {
  background-color: rgba(var(--primary-color-rgb), 0.1);
}

.loading {
  opacity: 0.7;
  cursor: not-allowed;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.chat-box {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}
.message {
  display: flex;
  flex-direction: column;
  max-width: 80%;
}
.message.agent {
  align-self: flex-start;
  background-color: #f1f1ff;
  padding: 1rem;
  border-radius: 12px;
}
.message.student {
  align-self: flex-end;
  background-color: #d1fcd3;
  padding: 1rem;
  border-radius: 12px;
}
.label {
  font-size: 0.75rem;
  font-weight: bold;
  margin-bottom: 0.25rem;
}

</style>