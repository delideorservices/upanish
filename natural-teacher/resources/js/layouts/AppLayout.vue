<template>
    <div class="app-layout">
      <header class="app-header">
        <div class="container">
          <div class="logo">
            <router-link to="/">
              <h1>UpanishadAI</h1>
            </router-link>
          </div>
          
          <nav class="main-nav">
            <ul>
              <li><router-link to="/">Home</router-link></li>
              <li><router-link to="/about">About</router-link></li>
              <li><router-link to="/subjects">Subjects</router-link></li>
              <li><router-link to="/login">Login</router-link></li>
              <li><router-link to="/register">Register</router-link></li>
            </ul>
          </nav>
        </div>
      </header>
      
      <main class="app-content">
        <div class="container">
          <slot></slot>
        </div>
      </main>
      
      <footer class="app-footer">
        <div class="container">
          <p>&copy; {{ currentYear }} UpanishadAI. All rights reserved.</p>
        </div>
      </footer>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  
  const currentYear = computed(() => new Date().getFullYear());
  </script>
  
  <style scoped>
  .app-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  
  .app-header {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem 0;
  }
  
  .container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
  }
  
  .app-header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .logo a {
    color: white;
    text-decoration: none;
  }
  
  .logo h1 {
    margin: 0;
    font-size: 1.5rem;
  }
  
  .main-nav ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 1.5rem;
  }
  
  .main-nav a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: background-color 0.3s;
  }
  
  .main-nav a:hover,
  .main-nav a.router-link-active {
    background-color: rgba(255, 255, 255, 0.2);
  }
  
  .app-content {
    flex: 1;
    padding: 2rem 0;
  }
  
  .app-footer {
    background-color: #f5f5f5;
    color: #666;
    padding: 1.5rem 0;
    text-align: center;
  }
  </style>