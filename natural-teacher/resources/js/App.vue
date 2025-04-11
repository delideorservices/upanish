<template>
    <router-view />
  </template>
  
  <script setup>
  import { onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from './stores/auth';
  
  const router = useRouter();
  const authStore = useAuthStore();
  
  onMounted(async () => {
    // Check authentication status on app load
    await authStore.checkAuth();
    
    // Set theme based on user age group (for students)
    if (authStore.isAuthenticated && authStore.user.role === 'student') {
      const ageGroup = authStore.user.age_group_id;
      document.documentElement.setAttribute('data-theme', `theme-${ageGroup}`);
    }
  });
  </script>
  
  <style>
  @import 'animate.css';
  
  /* Global styles */
  :root {
    /* Default theme variables */
    --primary-color: #4a6bfa;
    --secondary-color: #45d9a8;
    --accent-color: #ffca28;
    --background-color: #ffffff;
    --text-color: #333333;
    --border-radius: 8px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --font-family: 'Nunito', sans-serif;
  }
  
  /* Age group specific theme variables will be applied via data-theme attributes */
  [data-theme="theme-1"] {
    /* Early Elementary (5-7) */
    --primary-color: #ff7043;
    --secondary-color: #4caf50;
    --accent-color: #ffeb3b;
    --border-radius: 16px;
    --font-size-base: 18px;
  }
  
  [data-theme="theme-2"] {
    /* Elementary (8-10) */
    --primary-color: #5c6bc0;
    --secondary-color: #26a69a;
    --accent-color: #ffa726;
    --border-radius: 12px;
    --font-size-base: 16px;
  }
  
  [data-theme="theme-3"] {
    /* Middle School (11-15) */
    --primary-color: #3949ab;
    --secondary-color: #00897b;
    --accent-color: #f57c00;
    --border-radius: 8px;
    --font-size-base: 14px;
  }
  
  body {
    font-family: var(--font-family);
    color: var(--text-color);
    background-color: var(--background-color);
    margin: 0;
    padding: 0;
    font-size: var(--font-size-base, 16px);
  }
  
  .btn {
    border-radius: var(--border-radius);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
  }
  
  .btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
  }
  
  .btn-secondary {
    background-color: var(--secondary-color);
    color: white;
    border: none;
  }
  
  .card {
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    background-color: var(--background-color);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  
  /* Animation keyframes for gamification */
  @keyframes celebrate {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
  }
  
  .celebrate {
    animation: celebrate 0.5s ease-in-out;
  }
  
  @keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-30px); }
    60% { transform: translateY(-15px); }
  }
  
  .bounce {
    animation: bounce 1s ease;
  }
  
  @keyframes sparkle {
    0% { filter: brightness(1); }
    50% { filter: brightness(1.5); }
    100% { filter: brightness(1); }
  }
  
  .sparkle {
    animation: sparkle 1s linear infinite;
  }
  </style>