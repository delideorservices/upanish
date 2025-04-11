<template>
    <div class="monitor-layout">
      <header class="header">
        <div class="container header-container">
          <div class="logo">
            <router-link to="/monitor/dashboard">
              <h1>UpanishadAI</h1>
            </router-link>
          </div>
          
          <nav class="navigation">
            <router-link to="/monitor/dashboard" class="nav-item">
              <i class="fas fa-tachometer-alt"></i>
              <span>Dashboard</span>
            </router-link>
            
            <router-link to="/monitor/students" class="nav-item">
              <i class="fas fa-users"></i>
              <span>Students</span>
            </router-link>
            
            <router-link to="/monitor/reports" class="nav-item">
              <i class="fas fa-chart-line"></i>
              <span>Reports</span>
            </router-link>
            
            <router-link to="/monitor/settings" class="nav-item">
              <i class="fas fa-cog"></i>
              <span>Settings</span>
            </router-link>
          </nav>
          
          <div class="user-menu">
            <div class="user-dropdown">
              <div class="avatar">
                <img src="/images/default-avatar.png" alt="Monitor avatar" />
              </div>
              <span class="username">Monitor</span>
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>
        </div>
      </header>
      
      <main class="main-content">
        <div class="container">
          <slot></slot>
        </div>
      </main>
      
      <footer class="footer">
        <div class="container">
          <p>&copy; {{ new Date().getFullYear() }} UpanishadAI. All rights reserved.</p>
        </div>
      </footer>
    </div>
  </template>
  
  <script setup>
  // Monitor layout script
  </script>
  
  <style scoped>
  .monitor-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  
  .header {
    background-color: var(--primary-color, #3949ab);
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
  
  .logo a {
    color: white;
    text-decoration: none;
  }
  
  .logo h1 {
    margin: 0;
    font-size: 1.5rem;
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
  
  .nav-item:hover,
  .nav-item.router-link-active {
    opacity: 1;
  }
  
  .nav-item i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
  }
  
  .user-menu {
    display: flex;
    align-items: center;
  }
  
  .user-dropdown {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--border-radius, 8px);
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