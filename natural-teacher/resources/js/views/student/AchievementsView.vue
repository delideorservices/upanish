<template>
    <StudentLayout>
      <div class="achievements-view">
        <h1 class="page-title">My Achievements</h1>
        
        <div class="stats-cards">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
              <h3>Total Achievements</h3>
              <p class="stat-value">{{ earnedAchievements.length }} / {{ achievements.length }}</p>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-medal"></i>
            </div>
            <div class="stat-content">
              <h3>Total Badges</h3>
              <p class="stat-value">{{ earnedBadges.length }} / {{ badges.length }}</p>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
              <h3>Total Points</h3>
              <p class="stat-value">{{ totalPoints }}</p>
            </div>
          </div>
        </div>
        
        <div class="tabs">
          <button 
            class="tab-button" 
            :class="{ 'active': activeTab === 'achievements' }"
            @click="activeTab = 'achievements'"
          >
            <i class="fas fa-trophy"></i> Achievements
          </button>
          <button 
            class="tab-button" 
            :class="{ 'active': activeTab === 'badges' }"
            @click="activeTab = 'badges'"
          >
            <i class="fas fa-medal"></i> Badges
          </button>
          <button 
            class="tab-button" 
            :class="{ 'active': activeTab === 'rewards' }"
            @click="activeTab = 'rewards'"
          >
            <i class="fas fa-gift"></i> Rewards
          </button>
        </div>
        
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading achievements...</p>
        </div>
        
        <!-- Achievements Tab -->
        <div v-else-if="activeTab === 'achievements'" class="tab-content">
          <div class="filter-buttons">
            <button 
              class="filter-button"
              :class="{ 'active': achievementFilter === 'all' }"
              @click="achievementFilter = 'all'"
            >
              All
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': achievementFilter === 'earned' }"
              @click="achievementFilter = 'earned'"
            >
              Earned
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': achievementFilter === 'locked' }"
              @click="achievementFilter = 'locked'"
            >
              Locked
            </button>
          </div>
          
          <div class="achievements-grid">
            <div 
              v-for="achievement in filteredAchievements" 
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
                  <i class="fas fa-lock"></i> Keep learning to unlock
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Badges Tab -->
        <div v-else-if="activeTab === 'badges'" class="tab-content">
          <div class="filter-buttons">
            <button 
              class="filter-button"
              :class="{ 'active': badgeFilter === 'all' }"
              @click="badgeFilter = 'all'"
            >
              All
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': badgeFilter === 'earned' }"
              @click="badgeFilter = 'earned'"
            >
              Earned
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': badgeFilter === 'locked' }"
              @click="badgeFilter = 'locked'"
            >
              Locked
            </button>
          </div>
          
          <div class="badges-grid">
            <div 
              v-for="badge in filteredBadges" 
              :key="badge.id"
              class="badge-card"
              :class="{ 'earned': badge.earned, 'locked': !badge.earned }"
            >
              <div class="badge-image">
                <img :src="badge.image_path || '/images/default-badge.png'" :alt="badge.name">
              </div>
              <h3>{{ badge.name }}</h3>
              <p class="badge-description">{{ badge.description }}</p>
              <div class="badge-details">
                <div v-if="badge.earned" class="badge-date">
                  Earned on {{ formatDate(badge.date_earned) }}
                </div>
                <div v-else class="badge-requirement">
                  <i class="fas fa-star"></i> Requires {{ badge.required_points }} points
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Rewards Tab -->
        <div v-else-if="activeTab === 'rewards'" class="tab-content">
          <div class="filter-buttons">
            <button 
              class="filter-button"
              :class="{ 'active': rewardFilter === 'all' }"
              @click="rewardFilter = 'all'"
            >
              All
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': rewardFilter === 'available' }"
              @click="rewardFilter = 'available'"
            >
              Available
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': rewardFilter === 'redeemed' }"
              @click="rewardFilter = 'redeemed'"
            >
              Redeemed
            </button>
            <button 
              class="filter-button"
              :class="{ 'active': rewardFilter === 'locked' }"
              @click="rewardFilter = 'locked'"
            >
              Locked
            </button>
          </div>
          
          <div class="rewards-grid">
            <div 
              v-for="reward in filteredRewards" 
              :key="reward.id"
              class="reward-card"
              :class="{
                'earned': reward.earned && !reward.redeemed,
                'redeemed': reward.redeemed,
                'locked': !reward.earned
              }"
            >
              <div class="reward-image">
                <img :src="reward.image_path || '/images/default-reward.png'" :alt="reward.name">
              </div>
              <h3>{{ reward.name }}</h3>
              <p class="reward-description">{{ reward.description }}</p>
              <div class="reward-details">
                <div v-if="reward.redeemed" class="reward-redeemed">
                  <i class="fas fa-check-circle"></i> Redeemed on {{ formatDate(reward.redemption_date) }}
                </div>
                <div v-else-if="reward.earned" class="reward-available">
                  <button class="btn btn-primary btn-sm" @click="redeemReward(reward.id)">
                    Redeem Reward
                  </button>
                </div>
                <div v-else class="reward-requirement">
                  <i class="fas fa-star"></i> Requires {{ reward.required_points }} points
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </StudentLayout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import axios from 'axios';
  
  // State
  const achievements = ref([]);
  const badges = ref([]);
  const rewards = ref([]);
  const totalPoints = ref(0);
  const loading = ref(false);
  const error = ref(null);
  const activeTab = ref('achievements');
  const achievementFilter = ref('all');
  const badgeFilter = ref('all');
  const rewardFilter = ref('all');
  
  // Computed properties
  const earnedAchievements = computed(() => {
    return achievements.value.filter(achievement => achievement.earned);
  });
  
  const earnedBadges = computed(() => {
    return badges.value.filter(badge => badge.earned);
  });
  
  const filteredAchievements = computed(() => {
    if (achievementFilter.value === 'earned') {
      return achievements.value.filter(achievement => achievement.earned);
    } else if (achievementFilter.value === 'locked') {
      return achievements.value.filter(achievement => !achievement.earned);
    } else {
      return achievements.value;
    }
  });
  
  const filteredBadges = computed(() => {
    if (badgeFilter.value === 'earned') {
      return badges.value.filter(badge => badge.earned);
    } else if (badgeFilter.value === 'locked') {
      return badges.value.filter(badge => !badge.earned);
    } else {
      return badges.value;
    }
  });
  
  const filteredRewards = computed(() => {
    if (rewardFilter.value === 'available') {
      return rewards.value.filter(reward => reward.earned && !reward.redeemed);
    } else if (rewardFilter.value === 'redeemed') {
      return rewards.value.filter(reward => reward.redeemed);
    } else if (rewardFilter.value === 'locked') {
      return rewards.value.filter(reward => !reward.earned);
    } else {
      return rewards.value;
    }
  });
  
  // Fetch data on component mount
  onMounted(async () => {
    await fetchGamificationData();
  });
  
  // Methods
  const fetchGamificationData = async () => {
    loading.value = true;
    
    try {
      const response = await axios.get('/api/gamification/user-data');
      achievements.value = response.data.achievements;
      badges.value = response.data.badges;
      rewards.value = response.data.rewards;
      totalPoints.value = response.data.total_points;
    } catch (err) {
      error.value = 'Failed to load gamification data. Please try again.';
      console.error(err);
    } finally {
      loading.value = false;
    }
  };
  
  const redeemReward = async (rewardId) => {
    try {
      await axios.post(`/api/gamification/redeem-reward/${rewardId}`);
      
      // Update the reward status in local state
      const reward = rewards.value.find(r => r.id === rewardId);
      if (reward) {
        reward.redeemed = true;
        reward.redemption_date = new Date().toISOString();
      }
      
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to redeem reward. Please try again.';
      console.error(err);
    }
  };
  
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(date);
  };
  </script>
  <style scoped>
  .achievements-view {
    max-width: 1000px;
    margin: 0 auto;
  }
  
  .page-title {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    text-align: center;
  }
  
  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
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
    background-color: rgba(74, 107, 250, 0.1);
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
  
  .tabs {
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
  
  .filter-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }
  
  .filter-button {
    background-color: #f0f0f0;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-family: inherit;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .filter-button.active {
    background-color: var(--primary-color);
    color: white;
  }
  
  .loading-state {
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
  
  .loading-state i {
    font-size: 3rem;
    color: var(--primary-color);
    opacity: 0.5;
    margin-bottom: 1rem;
  }
  
  .achievements-grid, .badges-grid, .rewards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
  }
  
  .achievement-card, .badge-card, .reward-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    border: 2px solid transparent;
  }
  
  .achievement-card.earned {
    border-color: #4caf50;
  }
  
  .achievement-card.locked, .badge-card.locked, .reward-card.locked {
    opacity: 0.7;
  }
  
  .achievement-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    background-color: rgba(74, 107, 250, 0.1);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 1rem;
  }
  
  .badge-image, .reward-image {
    width: 100px;
    height: 100px;
    margin: 0 auto 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .badge-image img, .reward-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }
  
  .achievement-card h3, .badge-card h3, .reward-card h3 {
    margin-top: 0;
    margin-bottom: 0.75rem;
    text-align: center;
  }
  
  .achievement-description, .badge-description, .reward-description {
    text-align: center;
    color: #666;
    margin-bottom: 1rem;
    flex-grow: 1;
  }
  
  .achievement-details, .badge-details, .reward-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.875rem;
    border-top: 1px solid #eee;
    padding-top: 1rem;
  }
  
  .achievement-points, .badge-requirement, .reward-requirement {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #f57c00;
  }
  
  .achievement-date, .badge-date {
    color: #4caf50;
  }
  
  .achievement-locked, .reward-redeemed {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
  }
  
  .reward-available {
    display: flex;
    justify-content: center;
  }
  
  .btn-sm {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
  }
  </style>