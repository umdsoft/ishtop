/**
 * Vue I18n - Admin Panel Internationalization (uz/ru)
 */

import { createI18n } from 'vue-i18n';
import uz from '../locales/uz.json';
import ru from '../locales/ru.json';

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('kadrgo-admin-locale') || 'uz',
  fallbackLocale: 'uz',
  messages: { uz, ru },
  globalInjection: true,
});

export default i18n;
