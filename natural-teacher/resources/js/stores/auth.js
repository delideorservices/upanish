import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: false,
    loading: false,
    error: null
  }),
  
  getters: {
    isStudent: (state) => state.user?.role === 'student',
    isParent: (state) => state.user?.role === 'parent',
    isTeacher: (state) => state.user?.role === 'teacher',
    isAdmin: (state) => state.user?.role === 'admin',
  },
  
  actions: {
    async login(email, password) {
      this.loading = true;
      this.error = null;
      
      try {
        // Login with Laravel Sanctum
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/api/login', { email, password });
        
        this.user = response.data.user;
        this.token = response.data.token;
        this.isAuthenticated = true;
        
        // Save token to localStorage
        localStorage.setItem('token', this.token);
        localStorage.setItem('isAuthenticated', 'true');
        localStorage.setItem('userRole', this.user.role);
        
        // Set axios default authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async register(userData) {
      this.loading = true;
      this.error = null;
      
      try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/api/register', userData);
        
        this.user = response.data.user;
        this.token = response.data.token;
        this.isAuthenticated = true;
        
        // Save token to localStorage
        localStorage.setItem('token', this.token);
        localStorage.setItem('isAuthenticated', 'true');
        localStorage.setItem('userRole', this.user.role);
        
        // Set axios default authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async logout() {
      this.loading = true;
      
      try {
        await axios.post('/api/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        // Always clear local state even if API call fails
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        
        // Clear localStorage
        localStorage.removeItem('token');
        localStorage.removeItem('isAuthenticated');
        localStorage.removeItem('userRole');
        
        // Clear authorization header
        delete axios.defaults.headers.common['Authorization'];
        
        this.loading = false;
      }
    },
    
    async checkAuth() {
      if (!this.token) return false;
      
      this.loading = true;
      
      try {
        // Set authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        // Get current user
        const response = await axios.get('/api/user');
        this.user = response.data;
        this.isAuthenticated = true;
        localStorage.setItem('userRole', this.user.role);
        
        return true;
      } catch (error) {
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        
        // Clear localStorage
        localStorage.removeItem('token');
        localStorage.removeItem('isAuthenticated');
        localStorage.removeItem('userRole');
        
        // Clear authorization header
        delete axios.defaults.headers.common['Authorization'];
        
        return false;
      } finally {
        this.loading = false;
      }
    }
  }
});