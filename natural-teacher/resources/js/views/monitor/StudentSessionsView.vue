<template>
    <MonitorLayout>
      <div class="student-sessions-view">
        <div class="back-link">
          <router-link to="/monitor/dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
          </router-link>
        </div>
        
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading student sessions...</p>
        </div>
        
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <h3>Error Loading Data</h3>
          <p>{{ error }}</p>
          <button class="btn btn-primary" @click="fetchStudentSessions">
            Try Again
          </button>
        </div>
        
        <template v-else>
          <div class="student-header">
            <div class="student-profile">
              <div class="student-avatar">
                <img :src="student.avatar || '/images/default-avatar.png'" :alt="student.name">
              </div>
              <div class="student-info">
                <h1>{{ student.name }}'s Sessions</h1>
                <router-link :to="`/monitor/student/${studentId}/progress`" class="link-to-progress">
                  <i class="fas fa-chart-line"></i> View Progress
                </router-link>
              </div>
            </div>
            
            <div class="session-stats">
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                  <h3>Total Sessions</h3>
                  <p class="stat-value">{{ sessions.length }}</p>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                  <h3>This Week</h3>
                  <p class="stat-value">{{ sessionsThisWeek }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="filter-section">
            <div class="filter-controls">
              <div class="filter-group">
                <label for="subject-filter">Subject:</label>
                <select id="subject-filter" v-model="filters.subject" class="filter-select">
                  <option value="all">All Subjects</option>
                  <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                    {{ subject.name }}
                  </option>
                </select>
              </div>
              
              <div class="filter-group">
                <label for="date-filter">Time Period:</label>
                <select id="date-filter" v-model="filters.period" class="filter-select">
                  <option value="all">All Time</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="year">This Year</option>
                </select>
              </div>
              
              <button class="btn btn-primary btn-sm" @click="applyFilters">
                <i class="fas fa-filter"></i> Apply Filters
              </button>
            </div>
            
            <div class="search-box">
              <input 
                type="text" 
                v-model="filters.search" 
                placeholder="Search sessions..." 
                class="search-input"
                @keyup.enter="applyFilters"
              />
              <button class="search-button" @click="applyFilters">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
          
          <div v-if="filteredSessions.length === 0" class="empty-state">
            <i class="fas fa-history"></i>
            <h3>No sessions found</h3>
            <p>No sessions match your current filters. Try adjusting your search criteria.</p>
            <button class="btn btn-primary" @click="resetFilters">
              Reset Filters
            </button>
          </div>
          
          <div v-else class="sessions-list">
            <div 
              v-for="session in filteredSessions" 
              :key="session.id"
              class="session-card"
              :class="{ 'expanded': expandedSession === session.id }"
            >
              <div class="session-card-header" @click="toggleSession(session.id)">
                <div class="session-info">
                  <div class="subject-badge" :class="'subject-' + session.subject.id">
                    <i :class="getSubjectIcon(session.subject.id)"></i>
                    {{ session.subject.name }}
                  </div>
                  <h3>{{ session.topic.name }}</h3>
                  <p class="session-date">{{ formatDate(session.created_at) }}</p>
                </div>
                
                <div class="session-meta">
                  <div class="points-badge">
                    <i class="fas fa-star"></i>
                    {{ session.points_earned }} points
                  </div>
                  <div class="duration-badge">
                    <i class="fas fa-clock"></i>
                    {{ formatDuration(session.duration) }}
                  </div>
                  <div class="expand-button">
                    <i :class="expandedSession === session.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                  </div>
                </div>
              </div>
              
              <div class="session-card-content" v-if="expandedSession === session.id">
                <div v-if="session.questions && session.questions.length > 0">
                  <div class="question-section">
                    <h4>Student's Question:</h4>
                    <div class="question-content">
                      {{ session.questions[0].content }}
                      <img 
                        v-if="session.questions[0].file_path" 
                        :src="session.questions[0].file_path" 
                        alt="Question attachment" 
                        class="question-attachment"
                      />
                    </div>
                  </div>
                  
                  <div class="answer-section" v-if="session.questions[0].responses && session.questions[0].responses.length > 0">
                    <h4>AI Response:</h4>
                    <div class="answer-content" v-html="session.questions[0].responses[0].content"></div>
                  </div>
                </div>
                
                <div class="feedback-section">
                  <h4>Monitor Feedback</h4>
                  <div v-if="session.monitor_feedback" class="existing-feedback">
                    <p class="feedback-text">{{ session.monitor_feedback }}</p>
                    <p class="feedback-meta">Added on {{ formatDate(session.feedback_date) }}</p>
                  </div>
                  <div v-else class="add-feedback">
                    <textarea 
                      v-model="feedbackText" 
                      placeholder="Add your feedback about this session..."
                      rows="3"
                      class="feedback-input"
                    ></textarea>
                    <button 
                      class="btn btn-primary btn-sm" 
                      @click="submitFeedback(session.id)"
                      :disabled="!feedbackText.trim() || submittingFeedback"
                    >
                      <span v-if="!submittingFeedback">Submit Feedback</span>
                      <span v-else>
                        <i class="fas fa-spinner fa-spin"></i> Saving...
                      </span>
                    </button>
                  </div>
                </div>
                
                <div class="session-actions">
                  <button class="btn btn-outline btn-sm" @click="suggestNextSteps(session.id)">
                    <i class="fas fa-lightbulb"></i> Suggest Next Steps
                  </button>
                  <button class="btn btn-outline btn-sm" @click="createFollowUp(session.id)">
                    <i class="fas fa-plus"></i> Create Follow-up Challenge
                  </button>
                </div>
              </div>
            </div>
            
            <div class="pagination">
              <button 
                class="pagination-button" 
                :disabled="pagination.current_page === 1"
                @click="changePage(pagination.current_page - 1)"
              >
                <i class="fas fa-chevron-left"></i> Previous
              </button>
              
              <span class="page-info">Page {{ pagination.current_page }} of {{ pagination.total_pages }}</span>
              
              <button 
                class="pagination-button" 
                :disabled="pagination.current_page === pagination.total_pages"
                @click="changePage(pagination.current_page + 1)"
              >
                Next <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </template>
      </div>
    </MonitorLayout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import axios from 'axios';
  
  const route = useRoute();
  const studentId = computed(() => route.params.id);
  
  // State
  const student = ref({});
  const sessions = ref([]);
  const subjects = ref([]);
  const loading = ref(true);
  const error = ref(null);
  const expandedSession = ref(null);
  const feedbackText = ref('');
  const submittingFeedback = ref(false);
  const filters = ref({
    subject: 'all',
    period: 'all',
    search: ''
  });
  const pagination = ref({
    current_page: 1,
    total_pages: 1,
    total: 0
  });
  
  // Computed properties
  const filteredSessions = computed(() => {
    return sessions.value.filter(session => {
      // Filter by subject
      if (filters.value.subject !== 'all' && session.subject.id != filters.value.subject) {
        return false;
      }
      
      // Filter by period
      if (filters.value.period !== 'all') {
        const sessionDate = new Date(session.created_at);
        const now = new Date();
        
        if (filters.value.period === 'week') {
          const weekStart = new Date(now);
          weekStart.setDate(now.getDate() - now.getDay());
          weekStart.setHours(0, 0, 0, 0);
          if (sessionDate < weekStart) {
            return false;
          }
        }
        else if (filters.value.period === 'month') {
          const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
          if (sessionDate < monthStart) {
            return false;
          }
        }
        else if (filters.value.period === 'year') {
          const yearStart = new Date(now.getFullYear(), 0, 1);
          if (sessionDate < yearStart) {
            return false;
          }
        }
      }
      
      // Filter by search query
      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        const topicMatch = session.topic.name.toLowerCase().includes(searchTerm);
        const contentMatch = session.questions.some(q => 
          q.content.toLowerCase().includes(searchTerm)
        );
        
        if (!topicMatch && !contentMatch) {
          return false;
        }
      }
      
      return true;
    });
  });
  
  const sessionsThisWeek = computed(() => {
    const now = new Date();
    const weekStart = new Date(now);
    weekStart.setDate(now.getDate() - now.getDay());
    weekStart.setHours(0, 0, 0, 0);
    
    return sessions.value.filter(session => {
      const sessionDate = new Date(session.created_at);
      return sessionDate >= weekStart;
    }).length;
  });
  
  // Fetch data on component mount
  onMounted(async () => {
    await fetchStudentSessions();
  });
  
  // Methods
  const fetchStudentSessions = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      // Fetch student info and sessions in parallel
      const [studentResponse, sessionsResponse, subjectsResponse] = await Promise.all([
        axios.get(`/api/monitoring/student/${studentId.value}`),
        axios.get(`/api/monitoring/student/${studentId.value}/sessions`),
        axios.get('/api/subjects')
      ]);
      
      student.value = studentResponse.data;
      sessions.value = sessionsResponse.data.data;
      subjects.value = subjectsResponse.data;
      
      pagination.value = {
        current_page: sessionsResponse.data.current_page,
        total_pages: sessionsResponse.data.last_page,
        total: sessionsResponse.data.total
      };
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to load student sessions. Please try again.';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };
  
  const toggleSession = (sessionId) => {
    if (expandedSession.value === sessionId) {
      expandedSession.value = null;
    } else {
      expandedSession.value = sessionId;
    }
  };
  
  const submitFeedback = async (sessionId) => {
    if (!feedbackText.value.trim() || submittingFeedback.value) return;
    
    submittingFeedback.value = true;
    
    try {
      await axios.post(`/api/monitoring/session/${sessionId}/feedback`, {
        feedback: feedbackText.value
      });
      
      // Update session in local state
      const session = sessions.value.find(s => s.id === sessionId);
      if (session) {
        session.monitor_feedback = feedbackText.value;
        session.feedback_date = new Date().toISOString();
      }
      
      // Clear input
      feedbackText.value = '';
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to submit feedback. Please try again.';
      console.error(err);
    } finally {
      submittingFeedback.value = false;
    }
  };
  
  const applyFilters = () => {
    // In a real app, this would re-fetch data from the API with the filters
    // For now, we'll just use our computed property
  };
  
  const resetFilters = () => {
    filters.value = {
      subject: 'all',
      period: 'all',
      search: ''
    };
  };
  
  const changePage = (page) => {
    pagination.value.current_page = page;
    // In a real app, this would re-fetch data for the new page
  };
  
  const suggestNextSteps = (sessionId) => {
    // This would be implemented to suggest next steps based on the session
    alert('This feature would suggest personalized next steps for the student.');
  };
  
  const createFollowUp = (sessionId) => {
    // This would be implemented to create follow-up challenges
    alert('This feature would create a personalized follow-up challenge.');
  };
  
  const getSubjectIcon = (subjectId) => {
    return subjectId === 1 ? 'fas fa-calculator' : 'fas fa-book';
  };
  
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric'
    }).format(new Date(dateString));
  };
  
  const formatDuration = (seconds) => {
    if (!seconds) return '0m';
    
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    
    if (minutes > 0) {
      return `${minutes}m ${remainingSeconds}s`;
    } else {
      return `${remainingSeconds}s`;
    }
  };
  </script>
  
  <style scoped>
  .student-sessions-view {
    max-width: 1000px;
    margin: 0 auto;
  }
  
  .back-link {
    margin-bottom: 1.5rem;
  }
  
  .back-link a {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
  }
  
  .loading-state, .error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
  }
  
  .loading-state i, .error-state i {
    font-size: 3rem;
    color: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 1rem;
  }
  
  .error-state i {
    color: #f44336;
  }
  
  .student-header {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  
  @media (min-width: 768px) {
    .student-header {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }
  
  .student-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .student-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
  }
  
  .student-info h1 {
    margin: 0 0 0.25rem;
    font-size: 1.75rem;
  }
  
  .link-to-progress {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
  }
  
  .session-stats {
    display: flex;
    gap: 1rem;
  }
  
  .stat-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .stat-icon {
    width: 40px;
    height: 40px;
    background-color: rgba(74, 107, 250, 0.1);
    color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.25rem;
  }
  
  .stat-content h3 {
    margin: 0 0 0.25rem;
    font-size: 0.875rem;
    color: #666;
  }
  
  .stat-value {
    font-size: 1.25rem;
    font-weight: bold;
    margin: 0;
    color: var(--primary-color);
  }
  
  .filter-section {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1rem;
  }
  
  .filter-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
  }
  
  .filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .filter-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    background-color: white;
  }
  
  .search-box {
    display: flex;
    flex: 1;
    min-width: 200px;
  }
  
  .search-input {
    flex: 1;
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius) 0 0 var(--border-radius);
    font-family: inherit;
  }
  
  .search-button {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
    cursor: pointer;
  }
  
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
  }
  
  .empty-state i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 1rem;
  }
  
  .sessions-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .session-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    transition: all 0.3s ease;
  }
  
  .session-card.expanded {
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
  }
  
  .session-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    cursor: pointer;
    border-bottom: 1px solid transparent;
    transition: all 0.3s ease;
  }
  
  .expanded .session-card-header {
    border-bottom-color: #eee;
  }
  
  .session-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .subject-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    margin-bottom: 0.5rem;
  }
  
  .subject-1 {
    background-color: rgba(74, 107, 250, 0.1);
    color: var(--primary-color);
  }
  
  .subject-2 {
    background-color: rgba(69, 217, 168, 0.1);
    color: var(--secondary-color);
  }
  
  .session-date {
    font-size: 0.875rem;
    color: #666;
    margin: 0;
  }
  
  .session-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .points-badge, .duration-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: rgba(255, 202, 40, 0.1);
    color: #f57c00;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
  }
  
  .duration-badge {
    background-color: rgba(69, 217, 168, 0.1);
    color: var(--secondary-color);
  }
  
  .session-card-content {
    padding: 1.5rem;
  }
  
  .question-section, .answer-section, .feedback-section {
    margin-bottom: 1.5rem;
  }
  
  .question-section h4, .answer-section h4, .feedback-section h4 {
    margin-top: 0;
    margin-bottom: 0.75rem;
    color: #333;
  }
  
  .question-content, .answer-content {
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    padding: 1rem;
  }
  
  .question-attachment {
    display: block;
    max-width: 100%;
    margin-top: 1rem;
    border-radius: var(--border-radius);
  }
  
  .existing-feedback {
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    padding: 1rem;
  }
  
  .feedback-text {
    margin: 0 0 0.5rem;
  }
  
  .feedback-meta {
    margin: 0;
    font-size: 0.875rem;
    color: #666;
    text-align: right;
  }
  
  .add-feedback {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .feedback-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-family: inherit;
    resize: vertical;
  }
  
  .session-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }
  
  .btn-outline {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
  }
  
  .btn-sm {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding: 1rem;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
  }
  
  .pagination-button {
    background-color: transparent;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .pagination-button:hover:not(:disabled) {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }
  
  .pagination-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  @media (max-width: 768px) {
    .student-profile {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    
    .session-meta {
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .session-card-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
  }
  </style>