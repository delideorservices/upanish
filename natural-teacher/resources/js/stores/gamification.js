import { defineStore } from 'pinia';
import axios from 'axios';

export const useGamificationStore = defineStore('gamification', {
  state: () => ({
    achievements: [],
    badges: [],
    rewards: [],
    userLevel: 1,
    totalPoints: 0,
    pointsToNextLevel: 100,
    dailyStreak: 0,
    recentlyEarned: null,
    loading: false,
    error: null
  }),
  
  getters: {
    levelProgress: (state) => {
      const currentLevelThreshold = 100 + ((state.userLevel - 1) * 50);
      const nextLevelThreshold = 100 + (state.userLevel * 50);
      const pointsInCurrentLevel = state.totalPoints - currentLevelThreshold;
      const pointsNeededForNextLevel = nextLevelThreshold - currentLevelThreshold;
      
      return Math.min(100, Math.round((pointsInCurrentLevel / pointsNeededForNextLevel) * 100));
    },
    
    earnedAchievements: (state) => {
      return state.achievements.filter(achievement => achievement.earned);
    },
    
    availableRewards: (state) => {
      return state.rewards.filter(reward => !reward.redeemed);
    }
  },
  
  actions: {
    async loadUserGamificationData() {
      this.loading = true;
      
      try {
        const response = await axios.get('/api/gamification/user-data');
        
        this.userLevel = response.data.level;
        this.totalPoints = response.data.total_points;
        this.pointsToNextLevel = response.data.points_to_next_level;
        this.dailyStreak = response.data.daily_streak;
        this.achievements = response.data.achievements;
        this.badges = response.data.badges;
        this.rewards = response.data.rewards;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load gamification data';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async checkForNewAchievements() {
      this.loading = true;
      
      try {
        const response = await axios.post('/api/gamification/check-achievements');
        // print(response);
        if (response.data.new_achievements && response.data.new_achievements.length > 0) {
          this.recentlyEarned = response.data.new_achievements[0];
          
          // Update achievements list
          this.achievements = this.achievements.map(achievement => {
            const newAchievement = response.data.new_achievements.find(a => a.id === achievement.id);
            if (newAchievement) {
              return { ...achievement, earned: true, date_earned: newAchievement.date_earned };
            }
            return achievement;
          });
          
          // Update user points and level
          if (response.data.updated_points) {
            this.totalPoints = response.data.updated_points;
          }
          
          if (response.data.updated_level && response.data.updated_level !== this.userLevel) {
            this.userLevel = response.data.updated_level;
            this.pointsToNextLevel = 100 + (this.userLevel * 50);
          }
        }
        
        return response.data.new_achievements || [];
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to check for achievements';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async redeemReward(rewardId) {
      this.loading = true;
      
      try {
        const response = await axios.post(`/api/gamification/redeem-reward/${rewardId}`);
        
        // Update the reward in the store
        this.rewards = this.rewards.map(reward => {
          if (reward.id === rewardId) {
            return { ...reward, redeemed: true, redemption_date: new Date() };
          }
          return reward;
        });
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to redeem reward';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    addPoints(points) {
      // For immediate visual feedback before API response
      this.totalPoints += points;
      
      // Re-calculate level progress
      const currentLevelThreshold = 100 + ((this.userLevel - 1) * 50);
      const nextLevelThreshold = 100 + (this.userLevel * 50);
      
      // Check if level up is needed
      if (this.totalPoints >= nextLevelThreshold) {
        this.userLevel += 1;
        this.pointsToNextLevel = 100 + (this.userLevel * 50);
      }
    },
    
    clearRecentlyEarned() {
      this.recentlyEarned = null;
    }
  }
});