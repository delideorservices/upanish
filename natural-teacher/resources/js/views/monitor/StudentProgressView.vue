<template>
    <MonitorLayout>
      <div class="student-progress-view">
        <div class="back-link">
          <router-link to="/monitor/dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
          </router-link>
        </div>
        
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading student progress...</p>
        </div>
        
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <h3>Error Loading Data</h3>
          <p>{{ error }}</p>
          <button class="btn btn-primary" @click="fetchStudentProgress">
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
                <h1>{{ student.name }}</h1>
                <p class="student-details">{{ student.age }} years old • Level {{ student.level }}</p>
              </div>
            </div>
            
            <div class="student-stats">
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                  <h3>Total Points</h3>
                  <p class="stat-value">{{ student.total_points }}</p>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-book"></i>
                </div>
                <div class="stat-content">
                  <h3>Homework Sessions</h3>
                  <p class="stat-value">{{ totalSessions }}</p>
                </div>
              </div>
              
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fas fa-fire"></i>
                </div>
                <div class="stat-content">
                  <h3>Daily Streak</h3>
                  <p class="stat-value">{{ student.daily_streak }} days</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="progress-tabs">
            <button 
              class="tab-button" 
              :class="{ 'active': activeTab === 'overview' }"
              @click="activeTab = 'overview'"
            >
              <i class="fas fa-chart-pie"></i> Overview
            </button>
            <button 
              class="tab-button" 
              :class="{ 'active': activeTab === 'subjects' }"
              @click="activeTab = 'subjects'"
            >
              <i class="fas fa-book"></i> Subjects
            </button>
            <button 
              class="tab-button" 
              :class="{ 'active': activeTab === 'achievements' }"
              @click="activeTab = 'achievements'"
            >
              <i class="fas fa-trophy"></i> Achievements
            </button>
            <button 
              class="tab-button" 
              :class="{ 'active': activeTab === 'report' }"
              @click="activeTab = 'report'"
            >
              <i class="fas fa-file-alt"></i> Progress Report
            </button>
          </div>
          
          <!-- Overview Tab -->
          <div v-if="activeTab === 'overview'" class="tab-content">
            <div class="overview-section">
              <div class="level-progress-card">
                <h2>Level Progress</h2>
                <div class="level-info">
                  <div class="current-level">Level {{ student.level }}</div>
                  <div class="next-level">Level {{ student.level + 1 }}</div>
                </div>
                <div class="progress-bar-container">
                  <div class="progress-bar" :style="{ width: `${student.level_progress}%` }"></div>
                </div>
                <div class="progress-details">
                  {{ student.level_progress }}% complete • {{ pointsToNextLevel }} more points needed
                </div>
              </div>
              
              <div class="activity-trend-card">
                <h2>Activity Trends</h2>
                <div class="activity-chart">
                  <!-- Placeholder for activity chart -->
                  <div class="chart-placeholder">
                    <i class="fas fa-chart-line"></i>
                    <p>Activity chart would display here</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="recent-activity-card">
              <h2>Recent Activity</h2>
              
              <div v-if="recentActivity.length === 0" class="empty-state small">
                <i class="fas fa-history"></i>
                <p>No recent activity</p>
              </div>
              
              <div v-else class="activity-list">
                <div 
                  v-for="activity in recentActivity" 
                  :key="activity.id"
                  class="activity-item"
                >
                  <div class="activity-date">
                    {{ formatDate(activity.date) }}
                  </div>
                  <div class="activity-details">
                    <div class="activity-subject">
                      <i :class="getSubjectIcon(activity.subject)"></i>
                      {{ activity.subject }}
                    </div>
                    <div class="activity-topic">{{ activity.topic }}</div>
                    <div class="activity-points">
                      <i class="fas fa-star"></i> {{ activity.points_earned }} points
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="strengths-improvements-card">
              <div class="strengths-section">
                <h2>Strengths</h2>
                <ul class="strength-list">
                  <li v-for="(strength, index) in strengths" :key="index">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ strength }}</span>
                  </li>
                </ul>
              </div>
              
              <div class="improvements-section">
                <h2>Areas for Improvement</h2>
                <ul class="improvement-list">
                  <li v-for="(improvement, index) in areasForImprovement" :key="index">
                    <i class="fas fa-arrow-circle-up"></i>
                    <span>{{ improvement }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <!-- Subjects Tab -->
          <div v-else-if="activeTab === 'subjects'" class="tab-content">
            <div class="subject-tabs">
              <button 
                v-for="subject in subjectSummary" 
                :key="subject.id"
                class="subject-tab"
                :class="{ 'active': activeSubject === subject.id }"
                @click="activeSubject = subject.id"
              >
                <i :class="getSubjectIcon(subject.name)"></i>
                {{ subject.name }}
              </button>
            </div>
            
            <div class="subject-content">
              <div v-if="currentSubject" class="subject-details">
                <div class="subject-header">
                  <h2>{{ currentSubject.name }}</h2>
                  <div class="subject-summary">
                    <div class="summary-item">
                      <span class="label">Sessions:</span>
                      <span class="value">{{ currentSubject.session_count }}</span>
                    </div>
                    <div class="summary-item">
                      <span class="label">Points:</span>
                      <span class="value">{{ currentSubject.total_points }}</span>
                    </div>
                    <div class="summary-item">
                      <span class="label">Last Activity:</span>
                      <span class="value">{{ formatDate(currentSubject.last_activity) }}</span>
                    </div>
                  </div>
                </div>
                
                <div class="topic-performance">
                  <h3>Topic Performance</h3>
                  
                  <div class="topic-list">
                    <div 
                      v-for="topic in currentSubject.topics" 
                      :key="topic.id"
                      class="topic-item"
                    >
                      <div class="topic-header">
                        <h4>{{ topic.name }}</h4>
                        <div class="topic-sessions">{{ topic.session_count }} sessions</div>
                      </div>
                      
                      <div class="topic-progress">
                        <div class="progress-bar-container">
                          <div class="progress-bar" :style="{ width: `${topic.mastery_percent}%` }"></div>
                        </div>
                        <div class="progress-label">{{ topic.mastery_percent }}% mastery</div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="subject-recommendations">
                  <h3>Recommendations</h3>
                  <ul class="recommendation-list">
                    <li v-for="(recommendation, index) in currentSubject.recommendations" :key="index">
                      <i class="fas fa-lightbulb"></i>
                      <span>{{ recommendation }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Achievements Tab -->
          <div v-else-if="activeTab === 'achievements'" class="tab-content">
            <div class="achievements-header">
              <h2>Student Achievements</h2>
              <div class="achievement-summary">
                <span>{{ earnedAchievements.length }} of {{ achievements.length }} achievements earned</span>
              </div>
            </div>
            
            <div class="achievements-grid">
              <div 
                v-for="achievement in achievements" 
                :key="achievement.id"
                class="achievement-card"
                :class="{ 'earned': achievement.earned, 'locked': !achievement.earned }"
              >
                <div class="achievement-icon">
                  <i :class="achievement.icon || 'fas fa-award'"></i>
                </div>
                <h3>{{ achievement.name }}</h3>
                <p class="achievement-description">{{ achievement.description }}</p>
                <div class="achievement-details">
                  <div class="achievement-points">
                    <i class="fas fa-star"></i> {{ achievement.points_value }} points
                  </div>
                  <div v-if="achievement.earned" class="achievement-date">
                    Earned on {{ formatDate(achievement.date_earned) }}
                  </div>
                  <div v-else class="achievement-locked">
                    <i class="fas fa-lock"></i> Not yet earned
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Progress Report Tab -->
          <div v-else-if="activeTab === 'report'" class="tab-content">
            <div class="report-header">
              <h2>Progress Report</h2>
              <div class="report-actions">
                <button class="btn btn-primary btn-sm" @click="generateReport">
                  <i class="fas fa-sync"></i> Generate New Report
                </button>
                <button class="btn btn-outline btn-sm">
                  <i class="fas fa-download"></i> Export PDF
                </button>
              </div>
            </div>
            
            <div v-if="!progressReport" class="empty-state">
              <i class="fas fa-file-alt"></i>
              <h3>No Progress Report Available</h3>
              <p>Generate a new progress report to see detailed insights.</p>
              <button class="btn btn-primary" @click="generateReport">
                Generate Report
              </button>
            </div>
            
            <div v-else class="progress-report">
              <div class="report-meta">
                <div class="report-period">
                  <span class="label">Period:</span>
                  <span class="value">{{ formatDate(progressReport.period_start) }} - {{ formatDate(progressReport.period_end) }}</span>
                </div>
                <div class="report-generated">
                  <span class="label">Generated:</span>
                  <span class="value">{{ formatDate(progressReport.created_at) }}</span>
                </div>
              </div>
              
              <div class="report-section">
                <h3>Strengths</h3>
                <div class="report-content">
                  {{ progressReport.strengths }}
                </div>
              </div>
              
              <div class="report-section">
                <h3>Areas for Improvement</h3>
                <div class="report-content">
                  {{ progressReport.areas_for_improvement }}
                </div>
              </div>
              
              <div class="report-section">
                <h3>Recommendations</h3>
                <div class="report-content">
                  <div v-html="formattedRecommendations"></div>
                </div>
              </div>
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
  const subjectSummary = ref([]);
  const achievements = ref([]);
  const recentActivity = ref([]);
  const progressReport = ref(null);
  const loading = ref(true);
  const error = ref(null);
  const activeTab = ref('overview');
  const activeSubject = ref(null);
  const generatingReport = ref(false);
  
  // Computed properties
  const totalSessions = computed(() => {
    return subjectSummary.value.reduce((total, subject) => total + subject.session_count, 0);
  });
  
  const earnedAchievements = computed(() => {
    return achievements.value.filter(achievement => achievement.earned);
  });
  
  const pointsToNextLevel = computed(() => {
    if (!student.value) return 0;
    // Calculate based on current level and progress percentage
    const totalPointsNeeded = 100; // This would come from the API in a real app
    const pointsNeeded = Math.ceil(totalPointsNeeded * (100 - student.value.level_progress) / 100);
    return pointsNeeded;
  });
  
  const currentSubject = computed(() => {
    if (!activeSubject.value) {
      return subjectSummary.value[0] || null;
    }
    return subjectSummary.value.find(subject => subject.id === activeSubject.value) || null;
  });
  
  const formattedRecommendations = computed(() => {
    if (!progressReport.value?.recommendations) return '';
    // Convert newlines to HTML line breaks
    return progressReport.value.recommendations.replace(/\n/g, '<br>');
  });
  
  // Areas that would be fetched from the API in a real app
  const strengths = ref([
    'Consistent performance in Mathematics',
    'Strong grasp of basic arithmetic concepts',
    'Regular practice schedule'
  ]);
  
  const areasForImprovement = ref([
    'Needs more practice with fractions',
    'Can improve reading comprehension skills',
    'Should increase time spent on English homework'
  ]);
  
  // Fetch data on component mount
  onMounted(async () => {
    await fetchStudentProgress();
  });
  
  // Methods
  const fetchStudentProgress = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get(`/api/monitoring/student/${studentId.value}/progress`);
      
      student.value = response.data.student;
      subjectSummary.value = response.data.subject_summary;
      achievements.value = response.data.recent_achievements;
      recentActivity.value = response.data.recent_activity;
      progressReport.value = response.data.progress_report;
      
      // Set default active subject if we have subjects
      if (subjectSummary.value.length > 0 && !activeSubject.value) {
        activeSubject.value = subjectSummary.value[0].id;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to load student progress. Please try again.';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };
  
  const generateReport = async () => {
    if (generatingReport.value) return;
    
    generatingReport.value = true;
    
    try {
      const response = await axios.post(`/api/monitoring/generate-report/${studentId.value}`);
      progressReport.value = response.data.report;
      activeTab.value = 'report';
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to generate report. Please try again.';
      console.error(err);
    } finally {
      generatingReport.value = false;
    }
  };
  
  const getSubjectIcon = (subjectName) => {
    if (typeof subjectName === 'number') {
      return subjectName === 1 ? 'fas fa-calculator' : 'fas fa-book';
    }
    return subjectName.toLowerCase().includes('math') ? 'fas fa-calculator' : 'fas fa-book';
  };
  
  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(new Date(dateString));
  };
  </script>
  
  <style scoped>
  .student-progress-view {
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
  
  .student-details {
    margin: 0;
    color: #666;
  }
  
  .student-stats {
    display: flex;
    flex-wrap: wrap;
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
  
  .progress-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 2rem;
    overflow-x: auto;
  }
  
  .tab-button {
    background: none;
    border: none;
    padding: 1rem 1.5rem;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    white-space: nowrap;
  }
  
  .tab-button.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
  }
  
  .tab-button i {
    margin-right: 0.5rem;
  }
  
  .tab-content {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
  }
  
  .overview-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  @media (min-width: 768px) {
    .overview-section {
      grid-template-columns: 1fr 1fr;
    }
  }
  
  .level-progress-card, .activity-trend-card, .recent-activity-card, .strengths-improvements-card {
    padding: 1.5rem;
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
  }
  
  .level-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
  }
  
  .progress-bar-container {
    height: 10px;
    background-color: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 0.5rem;
  }
  
  .progress-bar {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 5px;
  }
  
  .progress-details {
    font-size: 0.875rem;
    color: #666;
  }
  
  .chart-placeholder {
    height: 150px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;
  }
  
  .chart-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
  }
  
  .activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .activity-item {
    display: flex;
    gap: 1rem;
  }
  
  .activity-date {
    min-width: 100px;
    font-size: 0.875rem;
    color: #666;
  }
  
  .activity-details {
    flex: 1;
  }
  
  .activity-subject {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
  }
  
  .activity-topic {
    font-size: 0.875rem;
    margin: 0.25rem 0;
  }
  
  .activity-points {
    font-size: 0.875rem;
    color: #f57c00;
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }
  
  .strengths-improvements-card {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  @media (min-width: 768px) {
    .strengths-improvements-card {
      grid-template-columns: 1fr 1fr;
    }
  }
  
  .strength-list, .improvement-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .strength-list li, .improvement-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }
  
  .strength-list i {
    color: #4caf50;
  }
  
  .improvement-list i {
    color: #ff9800;
  }
  
  .subject-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    overflow-x: auto;
  }
  
  .subject-tab {
    background: none;
    border: 1px solid #ddd;
    padding: 0.75rem 1.5rem;
    border-radius: 2rem;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .subject-tab.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }
  
  .subject-header {
    margin-bottom: 1.5rem;
  }
  
  .subject-header h2 {
    margin-top: 0;
    margin-bottom: 1rem;
  }
  
  .subject-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
  }
  
  .summary-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .summary-item .label {
    color: #666;
    font-weight: 600;
  }
  
  .topic-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-top: 1rem;
    margin-bottom: 1.5rem;
  }
  
  .topic-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
  }
  
  .topic-header h4 {
    margin: 0;
  }
  
  .topic-sessions {
    font-size: 0.875rem;
    color: #666;
  }
  
  .topic-progress {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .topic-progress .progress-bar-container {
    flex: 1;
  }
  
  .progress-label {
    min-width: 80px;
    font-size: 0.875rem;
    font-weight: 600;
  }
  
  .recommendation-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .recommendation-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }
  
  .recommendation-list i {
    color: var(--primary-color);
  }
  
  .achievements-header, .report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  
  .achievements-header h2, .report-header h2 {
    margin: 0;
  }
  
  .achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
  }
  
  .achievement-card {
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    border: 2px solid transparent;
  }
  
  .achievement-card.earned {
    border-color: #4caf50;
  }
  
  .achievement-card.locked {
    opacity: 0.7;
  }
  
  .achievement-icon {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
  }
  
  .achievement-card h3 {
    margin-top: 0;
    margin-bottom: 0.75rem;
  }
  
  .achievement-description {
    margin-bottom: 1rem;
    color: #666;
    flex-grow: 1;
  }
  
  .achievement-details {
    width: 100%;
    border-top: 1px solid #eee;
    padding-top: 1rem;
    font-size: 0.875rem;
  }
  
  .achievement-points {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #f57c00;
    margin-bottom: 0.5rem;
  }
  
  .achievement-date {
    color: #4caf50;
  }
  
  .achievement-locked {
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }
  
  .report-actions {
    display: flex;
    gap: 0.75rem;
  }
  
  .report-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
  }
  
  .report-period, .report-generated {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .report-period .label, .report-generated .label {
    font-weight: 600;
    color: #666;
  }
  
  .report-section {
    margin-bottom: 1.5rem;
  }
  
  .report-section h3 {
    margin-top: 0;
    margin-bottom: 0.75rem;
    font-size: 1.25rem;
    color: var(--primary-color);
  }
  
  .report-content {
    background-color: #f9f9f9;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    line-height: 1.6;
  }
  .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
}

.empty-state.small {
  padding: 1.5rem;
}

.empty-state i {
  font-size: 2.5rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.empty-state h3 {
  margin-top: 0;
  margin-bottom: 0.5rem;
}

.empty-state p {
  margin-bottom: 1.5rem;
  color: #666;
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

@media (max-width: 768px) {
  .student-profile {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  
  .subject-summary, .report-meta {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .achievements-header, .report-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>