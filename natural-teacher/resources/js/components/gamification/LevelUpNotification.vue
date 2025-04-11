<template>
    <div class="level-up-notification animate__animated animate__zoomIn">
      <div class="level-up-content">
        <div class="level-icon">
          <i class="fas fa-level-up-alt"></i>
        </div>
        
        <h2>Level Up!</h2>
        <h3>You reached Level {{ level }}</h3>
        
        <div class="confetti">
          <div v-for="n in 20" :key="n" class="confetti-piece" :style="confettiStyle(n)"></div>
        </div>
        
        <button class="btn btn-primary" @click="$emit('close')">
          Continue Learning
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  
  const props = defineProps({
    level: {
      type: Number,
      required: true
    }
  });
  
  defineEmits(['close']);
  
  // Generate random confetti styles
  const confettiStyle = (index) => {
    const colors = ['#FFC107', '#2196F3', '#4CAF50', '#E91E63', '#9C27B0'];
    const size = Math.floor(Math.random() * 10) + 5; // 5px to 15px
    const color = colors[index % colors.length];
    const left = Math.floor(Math.random() * 100) + '%';
    const animationDelay = (Math.random() * 2) + 's';
    const animationDuration = (Math.random() * 3 + 2) + 's';
    
    return {
      backgroundColor: color,
      width: `${size}px`,
      height: `${size}px`,
      left: left,
      animationDelay: animationDelay,
      animationDuration: animationDuration
    };
  };
  </script>
  
  <style scoped>
  .level-up-notification {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
  }
  
  .level-up-content {
    background-color: white;
    border-radius: var(--border-radius);
    padding: 3rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
  }
  
  .level-icon {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
  }
  
  h2 {
    color: var(--primary-color);
    font-size: 2.5rem;
    margin: 0 0 0.5rem;
  }
  
  h3 {
    font-size: 1.5rem;
    margin: 0 0 2rem;
    color: #666;
  }
  
  .btn {
    margin-top: 2rem;
    padding: 0.75rem 2rem;
    font-size: 1.25rem;
  }
  
  .confetti {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
  }
  
  .confetti-piece {
    position: absolute;
    top: -20px;
    animation: confettiFall linear forwards;
    border-radius: 2px;
    transform: rotate(0deg);
  }
  
  @keyframes confettiFall {
    0% {
      top: -20px;
      transform: rotate(0deg);
    }
    100% {
      top: 100%;
      transform: rotate(360deg);
    }
  }
  </style>