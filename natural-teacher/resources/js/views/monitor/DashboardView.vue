<template>
    <MonitorLayout>
      <div class="monitor-dashboard">
        <h1 class="page-title">Monitor Dashboard</h1>
        
        <div class="welcome-card">
          <h2>Welcome back, {{ user.name }}</h2>
          <p>Here you can monitor your students' learning progress, review their homework, and provide guidance.</p>
        </div>
        
        <div class="dashboard-grid">
          <div class="dashboard-main">
            <div class="students-section">
              <div class="section-header">
                <h2>My Students</h2>
                <button class="btn btn-primary btn-sm" @click="showAddStudentModal = true">
                  <i class="fas fa-plus"></i> Add Student
                </button>
              </div>
              
              <div v-if="loading.students" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading students...</p>
              </div>
              
              <div v-else-if="students.length === 0" class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No students yet</h3>
                <p>Add students to start monitoring their progress.</p>
                <button class="btn btn-primary" @click="showAddStudentModal = true">
                  <i class="fas fa-plus"></i> Add Student
                </button>
              </div>
              
              <div v-else class="students-list">
                <div 
                  v-for="student in students" 
                  :key="student.id"
                  class="student-card"
                >
                  <div class="student-avatar">
                    <img :src="student.avatar || '/images/default-avatar.png'" :alt="student.name">
                    <div class="student-level">L{{ student.level }}</div>
                  </div>
                  <div class="student-info">
                    <h3>{{ student.name }}</h3>
                    <p class="student-details">{{ student.age }} years old • {{ student.total_points }} points</p>
                  </div>
                  <div class="student-actions">
                    <router-link 
                      :to="`/monitor/student/${student.id}/progress`" 
                      class="btn btn-primary btn-sm"
                    >
                      <i class="fas fa-chart-line"></i> Progress
                    </router-link>
                    <router-link 
                      :to="`/monitor/student/${student.id}/sessions`" 
                      class="btn btn-outline btn-sm"
                    >
                      <i class="fas fa-history"></i> Sessions
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="recent-activity-section">
              <h2>Recent Activity</h2>
              
              <div v-if="loading.activity" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading activity...</p>
              </div>
              
              <div v-else-if="activities.length === 0" class="empty-state">
                <i class="fas fa-history"></i>
                <h3>No recent activity</h3>
                <p>Student activity will appear here.</p>
              </div>
              
              <div v-else class="activity-list">
                <div 
                  v-for="activity in activities" 
                  :key="activity.id"
                  class="activity-item"
                >
                  <div class="activity-icon">
                    <i :class="getActivityIcon(activity.type)"></i>
                  </div>
                  <div class="activity-content">
                    <p class="activity-text">
                      <strong>{{ activity.student.name }}</strong> {{ getActivityDescription(activity) }}
                    </p>
                    <p class="activity-time">{{ formatDate(activity.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="dashboard-sidebar">
            <div class="summary-section">
              <h2>Summary</h2>
              
              <div class="summary-stats">
                <div class="summary-stat">
                  <div class="stat-label">Students</div>
                  <div class="stat-value">{{ students.length }}</div>
                </div>
                <div class="summary-stat">
                  <div class="stat-label">Sessions Today</div>
                  <div class="stat-value">{{ todaySessions }}</div>
                </div>
                <div class="summary-stat">
                  <div class="stat-label">Average Score</div>
                  <div class="stat-value">{{ averageScore }}</div>
                </div>
              </div>
            </div>
            
            <div class="subject-performance-section">
              <h2>Subject Performance</h2>
              
              <div v-if="loading.performance" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading performance data...</p>
              </div>
              
              <div v-else-if="subjectPerformance.length === 0" class="empty-state">
                <i class="fas fa-chart-bar"></i>
                <h3>No performance data</h3>
                <p>Performance data will appear when students complete homework.</p>
              </div>
              
              <div v-else class="subject-performance-list">
                <div 
                  v-for="subject in subjectPerformance" 
                  :key="subject.id"
                  class="subject-performance-item"
                >
                  <div class="subject-info">
                    <i :class="getSubjectIcon(subject.id)"></i>
                    <div class="subject-name">{{ subject.name }}</div>
                  </div>
                  <div class="subject-progress">
                    <div class="progress-bar-container">
                      <div 
                        class="progress-bar" 
                        :style="{ width: `${subject.avg_score}%` }"
                        :class="`subject-${subject.id}`"
                      ></div>
                    </div>
                    <div class="progress-value">{{ subject.avg_score }}%</div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="upcoming-section">
              <h2>Suggested Actions</h2>
              
              <div class="suggestion-list">
                <div class="suggestion-item">
                  <i class="fas fa-user-plus"></i>
                  <p>Add new students to your monitoring list</p>
                </div>
                <div class="suggestion-item">
                  <i class="fas fa-comments"></i>
                  <p>Check in with students who haven't been active lately</p>
                </div>
                <div class="suggestion-item">
                  <i class="fas fa-book"></i>
                  <p>Review recent homework submissions</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Add Student Modal -->
        <div class="modal" v-if="showAddStudentModal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>Add Student</h2>
              <button class="close-button" @click="showAddStudentModal = false">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="addStudent">
                <div class="form-group">
                  <label for="student-email">Student Email</label>
                  <input 
                    type="email" 
                    id="student-email" 
                    v-model="newStudent.email" 
                    required
                    class="form-control"
                    placeholder="student@example.com"
                  />
                  <p class="form-help">Enter the email address of the student you want to monitor.</p>
                </div>
                
                <div class="form-group">
                  <label for="permission-level">Permission Level</label>
                  <select 
                    id="permission-level" 
                    v-model="newStudent.permission_level" 
                    required
                    class="form-control"
                  >
                    <option value="view">View Only</option>
                    <option value="interact">View & Interact</option>
                    <option value="manage">Full Access</option>
                  </select>
                  <p class="form-help">
                    <strong>View Only:</strong> See student progress and homework<br>
                    <strong>View & Interact:</strong> Additionally, provide feedback<br>
                    <strong>Full Access:</strong> Additionally, manage student settings
                  </p>
                </div>
                
                <div class="form-group">
                  <label for="notification-preferences">Notification Preferences</label>
                  <div class="checkbox-group">
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="newStudent.notifications.homework_submitted">
                      <span>Homework Submissions</span>
                    </label>
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="newStudent.notifications.achievements">
                      <span>New Achievements</span>
                    </label>
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="newStudent.notifications.inactivity">
                      <span>Inactivity Alerts</span>
                    </label>
                  </div>
                </div>
                
                <div v-if="error" class="error-message">
                  {{ error }}
                </div>
                
                <div class="form-actions">
                  <button type="button" class="btn btn-secondary" @click="showAddStudentModal = false">
                    Cancel
                  </button>
                  <button type="submit" class="btn btn-primary" :disabled="adding">
                    <span v-if="!adding">Add Student</span>
                    <span v-else>
                      <i class="fas fa-spinner fa-spin"></i> Adding...
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </MonitorLayout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import axios from 'axios';
  
  // State
  const user = ref({ name: 'Monitor' });
  const students = ref([]);
  const activities = ref([]);
  const subjectPerformance = ref([]);
  const loading = ref({
    students: false,
    activity: false,
    performance: false
  });
  const error = ref(null);
  const showAddStudentModal = ref(false);
  const newStudent = ref({
    email: '',
    permission_level: 'view',
    notifications: {
      homework_submitted: true,
      achievements: false,
      inactivity: true
    }
  });
  const adding = ref(false);
  
  // Computed
  const todaySessions = computed(() => {
    // Calculate from activities
    return activities.value.filter(a => 
      a.type === 'session_completed' && 
      new Date(a.created_at).toDateString() === new Date().toDateString()
    ).length;
  });
  
  const averageScore = computed(() => {
    // Calculate from subject performance
    if (subjectPerformance.value.length === 0) return 0;
    
    const sum = subjectPerformance.value.reduce((total, subject) => total + subject.avg_score, 0);
    return Math.round(sum / subjectPerformance.value.length);
  });
  
  // Fetch data on component mount
  onMounted(async () => {
    try {
      // Get current user info
      const userResponse = await axios.get('/api/user');
      user.value = userResponse.data;
      
      // Load data
      await Promise.all([
        fetchStudents(),
        fetchActivities(),
        fetchPerformance()
      ]);
    } catch (err) {
      error.value = 'Failed to load dashboard data. Please try again.';
      console.error(err);
    }
  });
  
  // Methods
  const fetchStudents = async () => {
    loading.value.students = true;
    
    try {
      const response = await axios.get('/api/monitoring/students');
      students.value = response.data;
    } catch (err) {
      console.error('Failed to load students:', err);
    } finally {
      loading.value.students = false;
    }
  };
  
  const fetchActivities = async () => {
    loading.value.activity = true;
    
    try {
      const response = await axios.get('/api/monitoring/activities');
      activities.value = response.data;
    } catch (err) {
      console.error('Failed to load activities:', err);
    } finally {
      loading.value.activity = false;
    }
  };
  
  const fetchPerformance = async () => {
    loading.value.performance = true;
    
    try {
      const response = await axios.get('/api/monitoring/performance');
      subjectPerformance.value = response.data;
    } catch (err) {
      console.error('Failed to load performance data:', err);
    } finally {
      loading.value.performance = false;
    }
  };
  
  const addStudent = async () => {
    adding.value = true;
    error.value = null;
    
    try {
      const response = await axios.post('/api/monitoring/add-student', {
        student_email: newStudent.value.email,
        permission_level: newStudent.value.permission_level,
        notification_preferences: newStudent.value.notifications
      });
      
      // Add new student to list
      students.value.push(response.data.monitoring.student);
      
      // Reset form and close modal
      newStudent.value = {
        email: '',
        permission_level: 'view',
        notifications: {
          homework_submitted: true,
          achievements: false,
          inactivity: true
        }
      };
      showAddStudentModal.value = false;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to add student. Please try again.';
    } finally {
      adding.value = false;
    }
  };
  
  const getActivityIcon = (type) => {
    switch (type) {
      case 'session_completed':
        return 'fas fa-check-circle';
      case 'achievement_earned':
        return 'fas fa-trophy';
      case 'level_up':
        return 'fas fa-arrow-up';
      default:
        return 'fas fa-bell';
    }
  };
  
  const getActivityDescription = (activity) => {
    switch (activity.type) {
      case 'session_completed':
        return `completed a ${activity.data.subject} homework session`;
      case 'achievement_earned':
        return `earned the "${activity.data.achievement_name}" achievement`;
      case 'level_up':
        return `reached level ${activity.data.new_level}`;
      default:
        return 'performed an activity';
    }
  };
  
  const getSubjectIcon = (subjectId) => {
    return subjectId === 1 ? 'fas fa-calculator' : 'fas fa-book';
  };
  
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    const now = new Date();
    
    // Today
    if (date.toDateString() === now.toDateString()) {
      return `Today, ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    }
    
    // Yesterday
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
      return `Yesterday, ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    }
    
    // Older
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric'
    }).format(date);
  };
  </script>
  
  <style scoped>
  .monitor-dashboard {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  .page-title {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    text-align: center;
  }
  
  .welcome-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .welcome-card h2 {
    margin-top: 0;
    color: var(--primary-color);
  }
  
  .welcome-card p {
    margin-bottom: 0;
    color: #666;
  }
  
  .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
  }
  
  @media (max-width: 992px) {
    .dashboard-grid {
      grid-template-columns: 1fr;
    }
  }
  
  .dashboard-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .students-section, .recent-activity-section, .summary-section, 
  .subject-performance-section, .upcoming-section {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
  }
  
  .section-header h2 {
    margin: 0;
  }
  
  .loading-state, .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
  }
  
  .loading-state i, .empty-state i {
    font-size: 2.5rem;
    color: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 1rem;
  }
  
  .students-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .student-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    gap: 1rem;
  }
  
  .student-avatar {
    position: relative;
  }
  
  .student-avatar img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
  }
  
  .student-level {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: var(--primary-color);
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .student-info {
    flex: 1;
  }
  
  .student-info h3 {
    margin: 0 0 0.25rem;
  }
  
  .student-details {
    margin: 0;
    font-size: 0.875rem;
    color: #666;
  }
  
  .student-actions {
    display: flex;
    gap: 0.5rem;
  }
  
  .activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .activity-item {
    display: flex;
    gap: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
  }
  
  .activity-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  
  .activity-icon {
    width: 40px;
    height: 40px;
    background-color: rgba(74, 107, 250, 0.1);
    color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
  }
  
  .activity-content {
    flex: 1;
  }
  
  .activity-text {
    margin: 0 0 0.25rem;
  }
  
  .activity-time {
    margin: 0;
    font-size: 0.875rem;
    color: #666;
  }
  
  .summary-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    text-align: center;
  }
  
  .summary-stat {
    padding: 1rem;
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
  }
  
  .stat-label {
    font-size: 0.875rem;
    color: #666;
    margin-bottom: 0.5rem;
  }
  
  .stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
  }
  
  .subject-performance-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .subject-performance-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .subject-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .subject-name {
    font-weight: 600;
  }
  
  .progress-bar-container {
    height: 8px;
    background-color: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
    flex: 1;
  }
  
  .progress-bar {
    height: 100%;
    border-radius: 4px;
  }
  
  .subject-progress {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .progress-value {
    font-size: 0.875rem;
    font-weight: 600;
    min-width: 40px;
    text-align: right;
  }
  
  .subject-1 {
    background-color: var(--primary-color);
  }
  
  .subject-2 {
    background-color: var(--secondary-color);
  }
  
  .suggestion-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .suggestion-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
  }
  
  .suggestion-item i {
    font-size: 1.25rem;
    color: var(--primary-color);
  }
  
  .suggestion-item p {
    margin: 0;
  }
  
  /* Modal styles */
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal-content {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
  }
  
  .modal-header h2 {
    margin: 0;
  }
  
  .close-button {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: #666;
  }
  
  .modal-body {
    padding: 1.5rem;
  }
  
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }
  
  .form-help {
    font-size: 0.875rem;
    color: #666;
    margin-top: 0.5rem;
  }
  </style>