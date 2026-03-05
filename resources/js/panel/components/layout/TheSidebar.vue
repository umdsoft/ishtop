<template>
  <aside
    :class="[
      'fixed top-0 left-0 z-50 h-screen transition-all duration-300 ease-in-out',
      'bg-surface-0 dark:bg-surface-900 border-r border-surface-200 dark:border-surface-800',
      'flex flex-col',
      sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
      'w-64',
    ]"
  >
    <!-- Logo -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-surface-200 dark:border-surface-800">
      <router-link to="/dashboard" class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center text-white font-bold text-lg">
          I
        </div>
        <span
          v-if="!sidebarCollapsed"
          class="text-xl font-bold text-surface-900 dark:text-surface-100 transition-opacity duration-200"
        >
          IshTop
        </span>
      </router-link>

      <!-- Mobile close button -->
      <button
        class="lg:hidden p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
        @click="closeMobileSidebar"
      >
        <XMarkIcon class="w-5 h-5" />
      </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
      <SidebarLink
        to="/dashboard"
        :icon="HomeIcon"
        :label="$t('sidebar.dashboard')"
        :collapsed="sidebarCollapsed"
      />

      <!-- Vakansiyalar Section -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Vakansiyalar
      </div>

      <SidebarLink
        to="/dashboard/vacancies"
        :icon="BriefcaseIcon"
        :label="$t('sidebar.vacancies')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/vacancies/create"
        :icon="PlusCircleIcon"
        :label="$t('sidebar.newVacancy')"
        :collapsed="sidebarCollapsed"
      />

      <!-- Rekruting Section -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Rekruting
      </div>

      <SidebarLink
        to="/dashboard/talent-pool"
        :icon="UsersIcon"
        :label="$t('sidebar.talentPool')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/questionnaires"
        :icon="ClipboardDocumentListIcon"
        :label="$t('sidebar.questionnaires')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/messages/templates"
        :icon="ChatBubbleLeftRightIcon"
        :label="$t('sidebar.messageTemplates')"
        :collapsed="sidebarCollapsed"
      />

      <!-- Reklama Section -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Reklama
      </div>

      <SidebarLink
        to="/dashboard/banners"
        :icon="RectangleStackIcon"
        :label="$t('sidebar.banners')"
        :collapsed="sidebarCollapsed"
      />

      <!-- Analitika Section -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Analitika
      </div>

      <SidebarLink
        to="/dashboard/analytics"
        :icon="ChartBarIcon"
        :label="$t('sidebar.analytics')"
        :collapsed="sidebarCollapsed"
      />

      <!-- Sozlamalar Section -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Sozlamalar
      </div>

      <SidebarLink
        to="/dashboard/settings/profile"
        :icon="UserCircleIcon"
        :label="$t('sidebar.profile')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/settings/company"
        :icon="BuildingOfficeIcon"
        :label="$t('sidebar.company')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/settings/billing"
        :icon="CreditCardIcon"
        :label="$t('sidebar.billing')"
        :collapsed="sidebarCollapsed"
      />
    </nav>

    <!-- Bottom section -->
    <div class="border-t border-surface-200 dark:border-surface-800 p-3 space-y-2">
      <!-- Theme toggle -->
      <button
        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
        @click="toggleTheme"
      >
        <component :is="isDark ? SunIcon : MoonIcon" class="w-5 h-5 text-surface-600 dark:text-surface-400" />
        <span v-if="!sidebarCollapsed" class="text-sm font-medium text-surface-700 dark:text-surface-300">
          {{ isDark ? 'Light mode' : 'Dark mode' }}
        </span>
      </button>

      <!-- Language toggle -->
      <button
        v-if="!sidebarCollapsed"
        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
        @click="toggleLocale"
      >
        <GlobeAltIcon class="w-5 h-5 text-surface-600 dark:text-surface-400" />
        <span class="text-sm font-medium text-surface-700 dark:text-surface-300">
          {{ locale === 'uz' ? 'uz' : 'ru' }}
        </span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useUIStore } from '../../stores/ui';
import SidebarLink from './SidebarLink.vue';
import {
  HomeIcon,
  BriefcaseIcon,
  PlusCircleIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  ChatBubbleLeftRightIcon,
  RectangleStackIcon,
  ChartBarIcon,
  UserCircleIcon,
  BuildingOfficeIcon,
  CreditCardIcon,
  MoonIcon,
  SunIcon,
  GlobeAltIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const uiStore = useUIStore();

const sidebarCollapsed = computed(() => uiStore.sidebarCollapsed);
const sidebarMobileOpen = computed(() => uiStore.sidebarMobileOpen);
const isDark = computed(() => uiStore.isDark);
const locale = computed(() => uiStore.locale);

function closeMobileSidebar() {
  uiStore.closeMobileSidebar();
}

function toggleTheme() {
  uiStore.toggleTheme();
}

function toggleLocale() {
  const newLocale = locale.value === 'uz' ? 'ru' : 'uz';
  uiStore.setLocale(newLocale);
}
</script>
