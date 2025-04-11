<template>
    <StudentLayout>
      <div class="homework-history">
        <h1 class="page-title">Your Homework History</h1>
        
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
            
            <button class="btn btn-primary btn-sm" @click="fetchSessions">
              <i class="fas fa-filter"></i> Apply Filters
            </button>
          </div>
          
          <div class="search-box">
            <input 
              type="text" 
              v-model="filters.search" 
              placeholder="Search your homework..." 
              class="search-input"
              @keyup.enter="fetchSessions"
            />
            <button class="search-button" @click="fetchSessions">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
        
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading your homework history...</p>
        </div>
        
        <div v-else-if="sessions.length === 0" class="empty-state">
          <i class="fas fa-history"></i>
          <h3>No homework history yet</h3>
          <p>After you submit homework, your sessions will appear here.</p>
          <router-link to="/homework/submit" class="btn btn-primary">
            Submit Homework
          </router-link>
        </div>
        
        <div v-else class="history-list">
          <div 
            v-for="session in sessions" 
            :key="session.id"
            class="history-card"
            :class="{ 'expanded': expandedSession === session.id }"
          >
            <div class="history-card-header" @click="toggleSession(session.id)">
              <div class="session-info">
                <div class="subject-badge" :class="'subject-' + session.subject_id">
                  <i :class="getSubjectIcon(session.subject_id)"></i>
                  {{ session.subject?.name }}
                </div>
                <h3>{{ session.topic?.name }}</h3>
                <p class="session-date">{{ formatDate(session.created_at) }}</p>
              </div>
              
              <div class="session-meta">
                <div class="points-badge">
                  <i class="fas fa-star"></i>
                  {{ session.points_earned }} points
                </div>
                <div class="expand-button">
                  <i :class="expandedSession === session.id ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                </div>
              </div>
            </div>
            
            <div class="history-card-content" v-if="expandedSession === session.id">
              <div v-if="session.questions && session.questions.length > 0">
                <div class="question-section">
                  <h4>Your Question:</h4>
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
                  <h4>Solution:</h4>
                  <div class="answer-content" v-html="session.questions[0].responses[0].content"></div>
                </div>
              </div>
              
              <div class="session-actions">
                <button class="btn btn-outline btn-sm">
                  <i class="fas fa-redo"></i> Similar Problem
                </button>
                <button class="btn btn-outline btn-sm">
                  <i class="fas fa-bookmark"></i> Save
                </button>
                <button class="btn btn-outline btn-sm">
                  <i class="fas fa-share"></i> Share
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
      </div>
    </StudentLayout>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  
  // State
  const subjects = ref([]);
  const sessions = ref([]);
  const expandedSession = ref(null);
  const loading = ref(false);
  const error = ref(null);
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
  
  // Load data on component mount
  onMounted(async () => {
    try {
      // Fetch subjects for filter dropdown
      const subjectsResponse = await axios.get('/api/subjects');
      subjects.value = subjectsResponse.data;
      
      // Fetch initial sessions
      await fetchSessions();
    } catch (err) {
      error.value = 'Failed to load data. Please try again.';
      console.error(err);
    }
  });
  
  // Methods
  const fetchSessions = async () => {
    loading.value = true;
    
    try {
      const response = await axios.get('/api/homework/history', {
        params: {
          page: pagination.value.current_page,
          subject_id: filters.value.subject !== 'all' ? filters.value.subject : null,
          period: filters.value.period !== 'all' ? filters.value.period : null,
          search: filters.value.search || null
        }
      });
      
      sessions.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        total_pages: response.data.last_page,
        total: response.data.total
      };
    } catch (err) {
      error.value = 'Failed to load sessions. Please try again.';
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
  
  const changePage = (page) => {
    pagination.value.current_page = page;
    fetchSessions();
  };
  
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric'
    }).format(date);
  };
  
  const getSubjectIcon = (subjectId) => {
    return subjectId === 1 ? 'fas fa-calculator' : 'fas fa-book';
  };
  </script>
  
  <style scoped>
  .homework-history {
    max-width: 900px;
    margin: 0 auto;
  }
  
  .page-title {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    text-align: center;
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
  
  .loading-state, .empty-state {
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
  
  .loading-state i, .empty-state i {
    font-size: 3rem;
    color: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 1rem;
  }
  
  .history-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .history-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    transition: all 0.3s ease;
  }
  
  .history-card.expanded {
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
  }
  
  .history-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    cursor: pointer;
    border-bottom: 1px solid transparent;
    transition: all 0.3s ease;
  }
  
  .expanded .history-card-header {
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
  
  .points-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: rgba(255, 202, 40, 0.1);
    color: #f57c00;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-weight: 600;
  }
  
  .history-card-content {
    padding: 1.5rem;
  }
  
  .question-section, .answer-section {
    margin-bottom: 1.5rem;
  }
  
  .question-section h4, .answer-section h4 {
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
  
  .session-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
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
  </style>