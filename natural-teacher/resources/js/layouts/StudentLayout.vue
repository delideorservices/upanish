<template>
    <div class="student-layout">
      <header class="header">
        <div class="container header-container">
          <div class="logo">
            <router-link to="/dashboard">
              <img src="/images/logo.png" alt="UpanishadAI" />
            </router-link>
          </div>
          
          <nav class="navigation">
            <router-link to="/dashboard" class="nav-item">
              <i class="fas fa-home"></i>
              <span>Dashboard</span>
            </router-link>
            
            <router-link to="/homework/submit" class="nav-item">
              <i class="fas fa-pencil-alt"></i>
              <span>New Homework</span>
            </router-link>
            
            <router-link to="/homework/history" class="nav-item">
              <i class="fas fa-history"></i>
              <span>History</span>
            </router-link>
            
            <router-link to="/achievements" class="nav-item">
              <i class="fas fa-trophy"></i>
              <span>Achievements</span>
            </router-link>
          </nav>
          
          <div class="user-menu">
            <div class="level-badge" v-if="gamification.userLevel">
              Level {{ gamification.userLevel }}
            </div>
            
            <div class="points-display" v-if="gamification.totalPoints">
              <i class="fas fa-star"></i>
              {{ gamification.totalPoints }} points
            </div>
            
            <div class="user-dropdown" @click="toggleDropdown">
              <div class="avatar">
                <img :src="userAvatar" alt="User avatar" />
              </div>
              <span class="username">{{ auth.user?.name }}</span>
              <i class="fas fa-chevron-down"></i>
              
              <div class="dropdown-menu" v-if="showDropdown">
                <div class="dropdown-item">
                  <i class="fas fa-user"></i>
                  <span>Profile</span>
                </div>
                
                <div class="dropdown-item">
                  <i class="fas fa-cog"></i>
                  <span>Settings</span>
                </div>
                
                <div class="dropdown-divider"></div>
                
                <div class="dropdown-item" @click="logout">
                  <i class="fas fa-sign-out-alt"></i>
                  <span>Logout</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>
      
      <main class="main-content">
        <div class="container">
          <achievement-notification 
            v-if="gamification.recentlyEarned"
            :achievement="gamification.recentlyEarned"
            @close="gamification.clearRecentlyEarned"
          />
          
          <level-up-notification 
            v-if="showLevelUp"
            :level="gamification.userLevel"
            @close="clearLevelUpNotification"
          />
          
          <slot></slot>
        </div>
      </main>
      
      <footer class="footer">
        <div class="container">
          <p>&copy; {{ currentYear }} UpanishadAI. All rights reserved.</p>
        </div>
      </footer>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../stores/auth';
  import { useGamificationStore } from '../stores/gamification';
  import AchievementNotification from '../components/gamification/AchievementNotification.vue';
  import LevelUpNotification from '../components/gamification/LevelUpNotification.vue';
  
  const router = useRouter();
  const auth = useAuthStore();
  const gamification = useGamificationStore();
  const showDropdown = ref(false);
  const showLevelUp = ref(false);
  const previousLevel = ref(null);
  
  const userAvatar = computed(() => {
    return auth.user?.profile?.avatar || '/images/default-avatar.png';
  });
  
  const currentYear = computed(() => {
    return new Date().getFullYear();
  });
  
  onMounted(async () => {
    // Load gamification data
    await gamification.loadUserGamificationData();
    previousLevel.value = gamification.userLevel;
    
    // Check for new achievements
    await gamification.checkForNewAchievements();
    
    // Check for level up
    if (gamification.userLevel > previousLevel.value) {
      showLevelUp.value = true;
    }
  });
  
  const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
  };
  
  const logout = async () => {
    await auth.logout();
    router.push('/login');
  };
  
  const clearLevelUpNotification = () => {
    showLevelUp.value = false;
    previousLevel.value = gamification.userLevel;
  };
  </script>
  
  <style scoped>
  .student-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  
  .header {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
  }
  
  .logo img {
    height: 40px;
  }
  
  .navigation {
    display: flex;
    gap: 1.5rem;
  }
  
  .nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: white;
    text-decoration: none;
    font-weight: 600;
    opacity: 0.8;
    transition: opacity 0.3s ease;
  }
  
  .nav-item:hover {
    opacity: 1;
  }
  
  .nav-item i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
  }
  
  .user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .level-badge {
    background-color: var(--accent-color);
    color: #333;
    font-weight: bold;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
  }
  
  .points-display {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 600;
  }
  
  .points-display i {
    color: var(--accent-color);
  }
  
  .user-dropdown {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: background-color 0.3s ease;
  }
  
  .user-dropdown:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .avatar img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
  }
  
  .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 200px;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 100;
    margin-top: 0.5rem;
  }
  
  .dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--text-color);
    transition: background-color 0.3s ease;
    cursor: pointer;
  }
  
  .dropdown-item:hover {
    background-color: #f5f5f5;
  }
  
  .dropdown-divider {
    height: 1px;
    background-color: #e0e0e0;
    margin: 0.5rem 0;
  }
  
  .main-content {
    flex: 1;
    padding: 2rem 0;
  }
  
  .footer {
    background-color: #f5f5f5;
    padding: 1.5rem 0;
    text-align: center;
    color: #666;
  }
  </style>