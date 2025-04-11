import { nextTick } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/auth/LoginView.vue';
import RegisterView from '../views/auth/RegisterView.vue';
import DashboardView from '../views/student/DashboardView.vue';
import HomeworkSubmitView from '../views/student/HomeworkSubmitView.vue';
import HomeworkHistoryView from '../views/student/HomeworkHistoryView.vue';
import AchievementsView from '../views/student/AchievementsView.vue';
import MonitorDashboardView from '../views/monitor/DashboardView.vue';
import StudentProgressView from '../views/monitor/StudentProgressView.vue';
import StudentSessionsView from '../views/monitor/StudentSessionsView.vue';
import NotFoundView from '../views/NotFoundView.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: HomeView,
        meta: { title: 'Welcome to UpanishadAI' }
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { title: 'Login to UpanishadAI' }
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
        meta: { title: 'Register for UpanishadAI' }
    },
    // Student routes
    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['student'],
            title: 'Student Dashboard'
        }
    },
    {
        path: '/homework/submit',
        name: 'homework.submit',
        component: HomeworkSubmitView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['student'],
            title: 'Submit Homework'
        }
    },
    {
        path: '/homework/history',
        name: 'homework.history',
        component: HomeworkHistoryView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['student'],
            title: 'Homework History'
        }
    },
    {
        path: '/achievements',
        name: 'achievements',
        component: AchievementsView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['student'],
            title: 'My Achievements'
        }
    },
    // Monitor routes (parent/teacher)
    {
        path: '/monitor/dashboard',
        name: 'monitor.dashboard',
        component: MonitorDashboardView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['parent', 'teacher'],
            title: 'Monitor Dashboard'
        }
    },
    {
        path: '/monitor/student/:id/progress',
        name: 'monitor.student.progress',
        component: StudentProgressView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['parent', 'teacher'],
            title: 'Student Progress'
        }
    },
    {
        path: '/monitor/student/:id/sessions',
        name: 'monitor.student.sessions',
        component: StudentSessionsView,
        meta: { 
            requiresAuth: true, 
            allowedRoles: ['parent', 'teacher'],
            title: 'Student Sessions'
        }
    },
    // 404 route
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundView,
        meta: { title: 'Page Not Found' }
    }
];

// Create router instance
const router = createRouter({
    history: createWebHistory(),
    routes
});

// Global navigation guard for auth and route metadata
router.beforeEach((to, from, next) => {
    // Check if route requires authentication
    if (to.meta.requiresAuth) {
        // Check if user is authenticated (we'll use a store later)
        const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
        const userRole = localStorage.getItem('userRole') || '';
        
        if (!isAuthenticated) {
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }
        
        // Check if user has required role
        if (to.meta.allowedRoles && !to.meta.allowedRoles.includes(userRole)) {
            next({ name: 'dashboard' });
            return;
        }
    }
    
    next();
});

// Update document title based on route meta
router.afterEach((to) => {
    nextTick(() => {
        document.title = to.meta.title || 'UpanishadAI';
    });
});

export default router;