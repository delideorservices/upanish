<template>
    <AppLayout>
      <div class="home-view">
        <div class="hero-section">
          <h1>Welcome to UpanishadAI</h1>
          <p>Your AI-powered homework assistant</p>
          <div class="cta-buttons">
            <router-link to="/register" class="btn btn-primary">Get Started</router-link>
            <router-link to="/about" class="btn btn-secondary">Learn More</router-link>
          </div>
        </div>
        
        <div class="features-section">
          <h2>How UpanishadAI helps students</h2>
          <div class="features-grid">
            <div class="feature-card">
              <i class="fas fa-brain"></i>
              <h3>Personalized Learning</h3>
              <p>Tailored assistance based on your age and learning style.</p>
            </div>
            
            <div class="feature-card">
              <i class="fas fa-puzzle-piece"></i>
              <h3>Step-by-Step Solutions</h3>
              <p>Clear explanations that help you understand concepts.</p>
            </div>
            
            <div class="feature-card">
              <i class="fas fa-trophy"></i>
              <h3>Fun Gamification</h3>
              <p>Earn points, badges, and achievements as you learn.</p>
            </div>
            
            <div class="feature-card">
              <i class="fas fa-user-graduate"></i>
              <h3>Parent & Teacher Monitoring</h3>
              <p>Progress tracking and insights for better support.</p>
            </div>
          </div>
        </div>
        
        <div class="subjects-section">
          <h2>Subjects We Cover</h2>
          <div class="subjects-grid">
            <div class="subject-card">
              <i class="fas fa-calculator"></i>
              <h3>Mathematics</h3>
              <p>From basic arithmetic to algebra and geometry.</p>
            </div>
            
            <div class="subject-card">
              <i class="fas fa-book"></i>
              <h3>English</h3>
              <p>Grammar, writing, reading comprehension, and more.</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  // Home view script
  </script>
  
  <style scoped>
  .hero-section {
    text-align: center;
    padding: 3rem 1rem;
    background-color: #f5f9ff;
    border-radius: var(--border-radius);
    margin-bottom: 3rem;
  }
  
  .hero-section h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
  }
  
  .hero-section p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    color: #666;
  }
  
  .cta-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
  }
  
  .features-section,
  .subjects-section {
    margin-bottom: 3rem;
  }
  
  .features-section h2,
  .subjects-section h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--primary-color);
  }
  
  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
  }
  
  .feature-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    text-align: center;
  }
  
  .feature-card i {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
  }
  
  .subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
  }
  
  .subject-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
    text-align: center;
  }
  
  .subject-card i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
  }
  </style>