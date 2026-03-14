/**
 * Admin UI Store - Theme, Sidebar, Locale state management
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useUIStore = defineStore('adminUi', () => {
  const isDark = ref(false);
  const sidebarCollapsed = ref(false);
  const sidebarMobileOpen = ref(false);
  const locale = ref('uz');

  const theme = computed(() => isDark.value ? 'dark' : 'light');

  function initializeTheme() {
    const storedTheme = localStorage.getItem('kadrgo-admin-theme');
    if (storedTheme) {
      isDark.value = storedTheme === 'dark';
    } else {
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
  }

  function toggleTheme() {
    isDark.value = !isDark.value;
    localStorage.setItem('kadrgo-admin-theme', isDark.value ? 'dark' : 'light');
  }

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('kadrgo-admin-sidebar', sidebarCollapsed.value ? '1' : '0');
  }

  function toggleMobileSidebar() {
    sidebarMobileOpen.value = !sidebarMobileOpen.value;
  }

  function closeMobileSidebar() {
    sidebarMobileOpen.value = false;
  }

  function initializeSidebar() {
    const collapsed = localStorage.getItem('kadrgo-admin-sidebar');
    if (collapsed) {
      sidebarCollapsed.value = collapsed === '1';
    }
  }

  function setLocale(newLocale) {
    locale.value = newLocale;
    localStorage.setItem('kadrgo-admin-locale', newLocale);
  }

  function initializeLocale() {
    const stored = localStorage.getItem('kadrgo-admin-locale');
    if (stored) {
      locale.value = stored;
    }
  }

  return {
    isDark,
    sidebarCollapsed,
    sidebarMobileOpen,
    locale,
    theme,
    initializeTheme,
    toggleTheme,
    toggleSidebar,
    toggleMobileSidebar,
    closeMobileSidebar,
    initializeSidebar,
    setLocale,
    initializeLocale,
  };
});
