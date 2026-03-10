/**
 * KadrGo Public Website - Entry Point
 * Vue 3 SPA for public-facing website
 */

import './bootstrap';
import '../css/website.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from '@website/router';
import i18n from '@website/plugins/i18n';

import App from '@website/App.vue';

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(i18n);

app.mount('#website-app');
