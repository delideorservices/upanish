<template>
    <StudentLayout>
      <div class="dashboard-view">
        <div class="dashboard-header">
          <h1>Welcome, Student!</h1>
          <p class="last-login">Last login: {{ formatDate(new Date()) }}</p>
        </div>
        
        <div class="stats-cards">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
              <h3>Homework Sessions</h3>
              <p class="stat-value">0</p>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
              <h3>Achievements</h3>
              <p class="stat-value">0</p>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-medal"></i>
            </div>
            <div class="stat-content">
              <h3>Current Streak</h3>
              <p class="stat-value">0 days</p>
            </div>
          </div>
        </div>
        
        <div class="progress-section">
          <h2>Your Progress</h2>
          
          <div class="level-progress">
            <div class="level-info">
              <h3>Level 1</h3>
              <p>Novice Learner</p>
            </div>
            
            <div class="progress-bar-container">
              <div class="progress-bar" :style="{ width: '25%' }"></div>
              <span class="progress-text">25% to Level 2</span>
            </div>
          </div>
        </div>
        
        <div class="subjects-section">
          <h2>Your Subjects</h2>
          
          <div class="subjects-grid">
            <div class="subject-card">
              <div class="subject-icon">
                <i class="fas fa-calculator"></i>
              </div>
              <h3>Mathematics</h3>
              <p>Explore math concepts and solve problems step-by-step.</p>
              <router-link to="/homework/submit" class="btn btn-primary">New Homework</router-link>
            </div>
            
            <div class="subject-card">
              <div class="subject-icon">
                <i class="fas fa-book"></i>
              </div>
              <h3>English</h3>
              <p>Improve your reading, writing, and language skills.</p>
              <router-link to="/homework/submit" class="btn btn-primary">New Homework</router-link>
            </div>
          </div>
        </div>
        
        <div class="recent-activity">
          <h2>Recent Activity</h2>
          
          <div class="empty-state">
            <i class="fas fa-history"></i>
            <p>No recent activity yet. Submit your first homework to get started!</p>
            <router-link to="/homework/submit" class="btn btn-secondary">Submit Homework</router-link>
          </div>
        </div>
      </div>
    </StudentLayout>
  </template>
  
  <script setup>
  // Format date helper function
  const formatDate = (date) => {
    return new Intl.DateTimeFormat('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: 'numeric',
      minute: 'numeric'
    }).format(date);
  };
  </script>
  
  <style scoped>
  .dashboard-view {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }
  
  .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  
  .dashboard-header h1 {
    color: var(--primary-color);
    margin: 0;
  }
  
  .last-login {
    color: #666;
    font-size: 0.9rem;
  }
  
  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
  }
  
  .stat-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .stat-icon {
    background-color: rgba(var(--primary-color-rgb, 74, 107, 250), 0.1);
    color: var(--primary-color);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.5rem;
  }
  
  .stat-content h3 {
    margin: 0 0 0.5rem;
    font-size: 1rem;
    color: #666;
  }
  
  .stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0;
    color: var(--primary-color);
  }
  
  .progress-section,
  .subjects-section,
  .recent-activity {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
  }
  
  .progress-section h2,
  .subjects-section h2,
  .recent-activity h2 {
    margin-top: 0;
    color: var(--primary-color);
    border-bottom: 1px solid #eee;
    padding-bottom: 0.75rem;
    margin-bottom: 1.5rem;
  }
  
  .level-progress {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .level-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .level-info h3 {
    margin: 0;
    font-size: 1.25rem;
  }
  
  .level-info p {
    margin: 0;
    color: #666;
  }
  
  .progress-bar-container {
    background-color: #f0f0f0;
    border-radius: 1rem;
    height: 1rem;
    position: relative;
    overflow: hidden;
  }
  
  .progress-bar {
    background-color: var(--accent-color);
    height: 100%;
    border-radius: 1rem;
  }
  
  .progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.75rem;
    font-weight: bold;
    color: #333;
  }
  
  .subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
  }
  
  .subject-card {
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: center;
    text-align: center;
  }
  
  .subject-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
  }
  
  .subject-card h3 {
    margin: 0;
  }
  
  .subject-card p {
    color: #666;
    margin-bottom: 1rem;
  }
  
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 2rem;
    color: #666;
  }
  
  .empty-state i {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 1rem;
  }
  
  .empty-state .btn {
    margin-top: 1.5rem;
  }
  </style>