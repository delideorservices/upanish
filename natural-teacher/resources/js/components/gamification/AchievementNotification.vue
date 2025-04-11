<template>
    <div class="achievement-notification animate__animated animate__bounceIn">
      <div class="achievement-icon">
        <i :class="achievement.icon"></i>
      </div>
      
      <div class="achievement-content">
        <h3>Achievement Unlocked!</h3>
        <h4>{{ achievement.name }}</h4>
        <p>{{ achievement.description }}</p>
        <div class="points">
          <i class="fas fa-star"></i>
          <span>+{{ achievement.points_value }} points</span>
        </div>
      </div>
      
      <button class="close-button" @click="$emit('close')">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </template>
  
  <script setup>
  defineProps({
    achievement: {
      type: Object,
      required: true
    }
  });
  
  defineEmits(['close']);
  </script>
  
  <style scoped>
  .achievement-notification {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    max-width: 400px;
  }
  
  .achievement-icon {
    font-size: 2.5rem;
    color: var(--accent-color);
    animation: sparkle 1s linear infinite;
  }
  
  .achievement-content h3 {
    color: var(--primary-color);
    margin: 0 0 0.25rem;
  }
  
  .achievement-content h4 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
  }
  
  .achievement-content p {
    margin: 0 0 0.75rem;
    color: #666;
  }
  
  .points {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: bold;
    color: #333;
  }
  
  .points i {
    color: var(--accent-color);
  }
  
  .close-button {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: none;
    border: none;
    font-size: 1rem;
    color: #999;
    cursor: pointer;
  }
  
  .close-button:hover {
    color: #666;
  }
  </style>