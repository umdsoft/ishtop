/**
 * Vue I18n - Internationalization (uz/ru)
 */

import { createI18n } from 'vue-i18n';

// Import translations
import uz from '../locales/uz.json';
import ru from '../locales/ru.json';

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('kadrgo-locale') || 'uz',
  fallbackLocale: 'uz',
  messages: {
    uz,
    ru,
  },
  globalInjection: true,
});

export default i18n;
