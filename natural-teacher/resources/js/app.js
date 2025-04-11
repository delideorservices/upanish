import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router'; // This imports the router instance, not just routes

// Import global components
import AppLayout from './layouts/AppLayout.vue';
import StudentLayout from './layouts/StudentLayout.vue';
import MonitorLayout from './layouts/MonitorLayout.vue';

// Create Pinia store
const pinia = createPinia();

// Create and mount app
const app = createApp(App);

// Register global components
app.component('AppLayout', AppLayout);
app.component('StudentLayout', StudentLayout);
app.component('MonitorLayout', MonitorLayout);

// Use plugins
app.use(router);
app.use(pinia);

app.mount('#app');