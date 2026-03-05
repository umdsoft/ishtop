/**
 * UI Store - Theme, Sidebar, Locale state management
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useUIStore = defineStore('ui', () => {
  // State
  const isDark = ref(false);
  const sidebarCollapsed = ref(false);
  const sidebarMobileOpen = ref(false);
  const locale = ref('uz');

  // Getters
  const theme = computed(() => isDark.value ? 'dark' : 'light');

  // Actions
  function initializeTheme() {
    // Check localStorage first
    const storedTheme = localStorage.getItem('ishtop-theme');
    if (storedTheme) {
      isDark.value = storedTheme === 'dark';
    } else {
      // Check system preference
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
  }

  function toggleTheme() {
    isDark.value = !isDark.value;
    localStorage.setItem('ishtop-theme', isDark.value ? 'dark' : 'light');
  }

  function setTheme(theme) {
    isDark.value = theme === 'dark';
    localStorage.setItem('ishtop-theme', theme);
  }

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('ishtop-sidebar-collapsed', sidebarCollapsed.value ? '1' : '0');
  }

  function toggleMobileSidebar() {
    sidebarMobileOpen.value = !sidebarMobileOpen.value;
  }

  function closeMobileSidebar() {
    sidebarMobileOpen.value = false;
  }

  function initializeSidebar() {
    const collapsed = localStorage.getItem('ishtop-sidebar-collapsed');
    if (collapsed) {
      sidebarCollapsed.value = collapsed === '1';
    }
  }

  function setLocale(newLocale) {
    locale.value = newLocale;
    localStorage.setItem('ishtop-locale', newLocale);
  }

  function initializeLocale() {
    const stored = localStorage.getItem('ishtop-locale');
    if (stored) {
      locale.value = stored;
    }
  }

  return {
    // State
    isDark,
    sidebarCollapsed,
    sidebarMobileOpen,
    locale,

    // Getters
    theme,

    // Actions
    initializeTheme,
    toggleTheme,
    setTheme,
    toggleSidebar,
    toggleMobileSidebar,
    closeMobileSidebar,
    initializeSidebar,
    setLocale,
    initializeLocale,
  };
});
