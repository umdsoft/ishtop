/**
 * KadrGo Recruiter Panel - Entry Point
 * Vue 3 SPA application for employer/recruiter dashboard
 */

import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './panel/router';
import i18n from './panel/plugins/i18n';
import { Toaster } from 'vue-sonner';

// Import root component
import App from './panel/App.vue';

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
