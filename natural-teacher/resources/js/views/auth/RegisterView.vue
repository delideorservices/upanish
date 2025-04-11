<template>
    <AppLayout>
      <div class="register-view">
        <div class="auth-card">
          <h1>Create an Account</h1>
          
          <form @submit.prevent="register" class="auth-form">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input 
                type="text" 
                id="name" 
                v-model="name" 
                required
                class="form-control"
                placeholder="Your Name"
                autocomplete="name"
              />
            </div>
            
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
              <label for="role">I am a:</label>
              <select 
                id="role" 
                v-model="role" 
                required
                class="form-control"
              >
                <option value="student">Student</option>
                <option value="parent">Parent</option>
                <option value="teacher">Teacher</option>
              </select>
            </div>
            
            <div class="form-group" v-if="role === 'student'">
              <label for="age">Age</label>
              <input 
                type="number" 
                id="age" 
                v-model="age" 
                required
                min="5"
                max="15"
                class="form-control"
                placeholder="Your age (5-15)"
              />
            </div>
            
            <div class="form-group" v-if="role === 'student'">
              <label for="learning_style">Learning Style</label>
              <select 
                id="learning_style" 
                v-model="learningStyle" 
                required
                class="form-control"
              >
                <option value="visual">Visual</option>
                <option value="auditory">Auditory</option>
                <option value="reading">Reading</option>
                <option value="kinesthetic">Hands-on</option>
                <option value="mixed">Mixed/Not Sure</option>
              </select>
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
                  autocomplete="new-password"
                  minlength="8"
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
            
            <div class="form-group">
              <label for="password_confirmation">Confirm Password</label>
              <div class="password-input">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="password_confirmation" 
                  v-model="passwordConfirmation" 
                  required
                  class="form-control"
                  placeholder="••••••••"
                  autocomplete="new-password"
                  minlength="8"
                />
              </div>
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
                <span v-if="!loading">Register</span>
                <span v-else>
                  <i class="fas fa-spinner fa-spin"></i> Creating Account...
                </span>
              </button>
            </div>
            
            <div class="auth-links">
              <router-link to="/login">Already have an account? Login</router-link>
            </div>
          </form>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import axios from 'axios';
  
  // Registration state
  const name = ref('');
  const email = ref('');
  const role = ref('student');
  const age = ref(10);
  const learningStyle = ref('mixed');
  const password = ref('');
  const passwordConfirmation = ref('');
  const showPassword = ref(false);
  const error = ref('');
  const loading = ref(false);
  
  const router = useRouter();
  const authStore = useAuthStore();
  
  // Make age field optional for non-students
  watch(role, (newValue) => {
    if (newValue !== 'student') {
      age.value = null;
    } else {
      age.value = 10;
    }
  });
  
  // Register function
  const register = async () => {
    loading.value = true;
    error.value = '';
    
    // Basic validation
    if (password.value !== passwordConfirmation.value) {
      error.value = 'Passwords do not match.';
      loading.value = false;
      return;
    }
    
    try {
      // Get CSRF cookie
      await axios.get('/sanctum/csrf-cookie');
      
      // Registration request
      const response = await axios.post('/api/register', {
        name: name.value,
        email: email.value,
        role: role.value,
        age: age.value,
        learning_style: learningStyle.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
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
      } else {
        router.push('/monitor/dashboard');
      }
    } catch (err) {
      console.error('Registration error:', err);
      if (err.response?.data?.errors) {
        // Handle validation errors
        const validationErrors = err.response.data.errors;
        const firstError = Object.values(validationErrors)[0];
        error.value = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        error.value = err.response?.data?.message || 'Registration failed. Please try again.';
      }
    } finally {
      loading.value = false;
    }
  };
  </script>
  
  
  <style scoped>
  .register-view {
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
    max-width: 500px;
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