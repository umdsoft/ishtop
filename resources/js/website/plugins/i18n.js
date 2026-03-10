/**
 * Vue I18n - Public Website (uz/ru)
 */

import { createI18n } from 'vue-i18n';
import uz from '@website/locales/uz.json';
import ru from '@website/locales/ru.json';

// Try cookie first (set by Laravel), then localStorage, fallback uz
function getInitialLocale() {
  const cookieMatch = document.cookie.match(/(?:^|;\s*)locale=(\w+)/);
  if (cookieMatch) return cookieMatch[1];
  return localStorage.getItem('kadrgo-web-locale') || 'uz';
}

const i18n = createI18n({
  legacy: false,
  locale: getInitialLocale(),
  fallbackLocale: 'uz',
  messages: { uz, ru },
  globalInjection: true,
});

export default i18n;
