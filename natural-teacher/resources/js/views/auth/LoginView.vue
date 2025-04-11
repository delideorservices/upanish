<template>
    <AppLayout>
      <div class="login-view">
        <div class="auth-card">
          <h1>Login to UpanishadAI</h1>
          
          <form @submit.prevent="login" class="auth-form">
            <div class="form-group">
              <label for="email">Email Address</label>
              <input 
                type="email" 
                id="email" 
                v-model="email" 
                required
                class="form-control"
                placeholder="your@email.com"
                autocomplete="email"
              />
            </div>
            
            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="password" 
                  v-model="password" 
                  required
                  class="form-control"
                  placeholder="••••••••"
                  autocomplete="current-password"
                />
                <button 
                  type="button" 
                  class="toggle-password"
                  @click="showPassword = !showPassword"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>
            
            <div class="form-check">
              <input type="checkbox" id="remember" v-model="remember" class="form-check-input">
              <label for="remember" class="form-check-label">Remember me</label>
            </div>
            
            <div v-if="error" class="error-message">
              {{ error }}
            </div>
            
            <div class="form-actions">
              <button 
                type="submit" 
                class="btn btn-primary" 
                :disabled="loading"
              >
                <span v-if="!loading">Login</span>
                <span v-else>
                  <i class="fas fa-spinner fa-spin"></i> Logging in...
                </span>
              </button>
            </div>
            
            <div class="auth-links">
              <router-link to="/forgot-password">Forgot your password?</router-link>
              <router-link to="/register">Don't have an account? Register</router-link>
            </div>
          </form>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import axios from 'axios';
  
  // Auth state
  const email = ref('');
  const password = ref('');
  const remember = ref(false);
  const showPassword = ref(false);
  const error = ref('');
  const loading = ref(false);
  
  const router = useRouter();
  const authStore = useAuthStore();
  
  // Login function
  const login = async () => {
    loading.value = true;
    error.value = '';
    
    try {
      // Get CSRF cookie
      await axios.get('/sanctum/csrf-cookie');
      
      // Login request
      const response = await axios.post('/api/login', {
        email: email.value,
        password: password.value,
      });
      
      // Update auth store
      authStore.user = response.data.user;
      authStore.token = response.data.token;
      authStore.isAuthenticated = true;
      
      // Save to localStorage
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('isAuthenticated', 'true');
      localStorage.setItem('userRole', response.data.user.role);
      
      // Set axios default headers
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
      
      // Redirect based on role
      if (response.data.user.role === 'student') {
        router.push('/dashboard');
      } else if (['parent', 'teacher'].includes(response.data.user.role)) {
        router.push('/monitor/dashboard');
      } else {
        router.push('/');
      }
    } catch (err) {
      console.error('Login error:', err);
      error.value = err.response?.data?.message || 'Login failed. Please check your credentials and try again.';
    } finally {
      loading.value = false;
    }
  };
  </script>
  
  <style scoped>
  .login-view {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem 1rem;
  }
  
  .auth-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 2rem;
    width: 100%;
    max-width: 450px;
  }
  
  .auth-card h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 2rem;
  }
  
  .auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }
  
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .form-group label {
    font-weight: 500;
  }
  
  .form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-family: inherit;
  }
  
  .password-input {
    position: relative;
  }
  
  .toggle-password {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
  }
  
  .form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .form-actions {
    margin-top: 1rem;
  }
  
  .form-actions .btn {
    width: 100%;
  }
  
  .error-message {
    color: #d32f2f;
    background-color: #ffebee;
    padding: 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.9rem;
  }
  
  .auth-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1.5rem;
    text-align: center;
  }
  
  .auth-links a {
    color: var(--primary-color);
    text-decoration: none;
  }
  
  .auth-links a:hover {
    text-decoration: underline;
  }
  </style>