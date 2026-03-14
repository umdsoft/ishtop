/**
 * KadrGo Admin Panel - Entry Point
 * Vue 3 SPA application for admin dashboard
 */

import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './admin/router/index';
import i18n from './admin/plugins/i18n';
import { Toaster } from 'vue-sonner';

// Import root component
import App from './admin/App.vue';

// Create Vue app
const app = createApp(App);

// Install plugins
app.use(createPinia());
app.use(router);
app.use(i18n);

// Global components
app.component('Toaster', Toaster);

// Mount app
app.mount('#app');
