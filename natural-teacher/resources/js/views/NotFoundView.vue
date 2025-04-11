<template>
    <AppLayout>
      <div class="not-found-view">
        <div class="not-found-content">
          <div class="error-code">404</div>
          <h1>Page Not Found</h1>
          <p>The page you're looking for doesn't exist or has been moved.</p>
          <div class="actions">
            <router-link to="/" class="btn btn-primary">
              <i class="fas fa-home"></i> Go Home
            </router-link>
            <button class="btn btn-secondary" @click="goBack">
              <i class="fas fa-arrow-left"></i> Go Back
            </button>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router';
  
  const router = useRouter();
  
  const goBack = () => {
    router.back();
  };
  </script>
  
  <style scoped>
  .not-found-view {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 1rem;
    text-align: center;
  }
  
  .not-found-content {
    max-width: 600px;
  }
  
  .error-code {
    font-size: 8rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 1rem;
    opacity: 0.5;
  }
  
  h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
  }
  
  p {
    font-size: 1.25rem;
    color: #666;
    margin-bottom: 2rem;
  }
  
  .actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
  }
  </style>