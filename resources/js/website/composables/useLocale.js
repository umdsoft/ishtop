/**
 * Locale composable — switch language, persist in localStorage + cookie
 */

import { useI18n } from 'vue-i18n';

export function useLocale() {
  const { locale } = useI18n();

  function setLocale(lang) {
    if (!['uz', 'ru'].includes(lang)) return;
    locale.value = lang;
    localStorage.setItem('kadrgo-web-locale', lang);
    // Set cookie so Laravel picks it up for SSR meta
    document.cookie = `locale=${lang};path=/;max-age=${365 * 24 * 60 * 60}`;
  }

  return { locale, setLocale };
}
