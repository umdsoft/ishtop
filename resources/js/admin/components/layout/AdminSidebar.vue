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
      <router-link to="/" class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center flex-shrink-0">
          <svg width="20" height="20" viewBox="0 0 48 48" fill="none">
            <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
          </svg>
        </div>
        <div v-if="!sidebarCollapsed" class="flex flex-col leading-none transition-opacity duration-200">
          <span class="text-[13px] font-extrabold tracking-[0.5px] text-brand-500">KADR</span>
          <span class="text-[13px] font-black tracking-[1px] text-accent-500 flex items-center">GO<span class="w-[5px] h-[5px] rounded-full bg-accent-500 ml-1"></span></span>
        </div>
      </router-link>

      <button
        class="lg:hidden p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
        @click="closeMobileSidebar"
      >
        <XMarkIcon class="w-5 h-5" />
      </button>
    </div>

    <!-- Admin badge -->
    <div v-if="!sidebarCollapsed" class="px-4 py-2 border-b border-surface-200 dark:border-surface-800">
      <div class="px-3 py-1.5 bg-danger-50 dark:bg-danger-950/30 rounded-lg text-center">
        <span class="text-xs font-bold text-danger-600 dark:text-danger-400 uppercase tracking-wider">Admin Panel</span>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
      <SidebarLink to="/" :icon="HomeIcon" :label="$t('sidebar.dashboard')" :collapsed="sidebarCollapsed" exact />

      <!-- Foydalanuvchilar -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Foydalanuvchilar
      </div>
      <SidebarLink to="/users" :icon="UsersIcon" :label="$t('sidebar.users')" :collapsed="sidebarCollapsed" />
      <SidebarLink to="/workers" :icon="WrenchScrewdriverIcon" :label="$t('sidebar.workers')" :collapsed="sidebarCollapsed" />
      <SidebarLink to="/employers" :icon="BuildingOfficeIcon" :label="$t('sidebar.employers')" :collapsed="sidebarCollapsed" />

      <!-- Katalog -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Katalog
      </div>
      <SidebarLink to="/categories" :icon="TagIcon" :label="$t('sidebar.categories')" :collapsed="sidebarCollapsed" />
      <SidebarLink to="/cities" :icon="MapPinIcon" :label="$t('sidebar.cities')" :collapsed="sidebarCollapsed" />

      <!-- Kontentlar -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Kontentlar
      </div>
      <SidebarLink to="/vacancies" :icon="BriefcaseIcon" :label="$t('sidebar.vacancies')" :collapsed="sidebarCollapsed" :badge="pendingCount" />
      <SidebarLink to="/applications" :icon="DocumentTextIcon" :label="$t('sidebar.applications')" :collapsed="sidebarCollapsed" />

      <!-- Moliya -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Moliya
      </div>
      <SidebarLink to="/payments" :icon="BanknotesIcon" :label="$t('sidebar.payments')" :collapsed="sidebarCollapsed" />
      <SidebarLink to="/subscriptions" :icon="CreditCardIcon" :label="$t('sidebar.subscriptions')" :collapsed="sidebarCollapsed" />

      <!-- Marketing -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Marketing
      </div>
      <SidebarLink to="/banners" :icon="PhotoIcon" :label="$t('sidebar.banners')" :collapsed="sidebarCollapsed" />

      <!-- Moderatsiya -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Moderatsiya
      </div>
      <SidebarLink to="/reports" :icon="FlagIcon" :label="$t('sidebar.reports')" :collapsed="sidebarCollapsed" :badge="reportsCount" badgeColor="danger" />

      <!-- Tizim -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Tizim
      </div>
      <SidebarLink to="/settings" :icon="CogIcon" :label="$t('sidebar.settings')" :collapsed="sidebarCollapsed" />
    </nav>

    <!-- Bottom section -->
    <div class="border-t border-surface-200 dark:border-surface-800 p-3 space-y-2">
      <button
        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
        @click="toggleTheme"
      >
        <component :is="isDark ? SunIcon : MoonIcon" class="w-5 h-5 text-surface-600 dark:text-surface-400" />
        <span v-if="!sidebarCollapsed" class="text-sm font-medium text-surface-700 dark:text-surface-300">
          {{ isDark ? 'Light mode' : 'Dark mode' }}
        </span>
      </button>

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
import { ref, computed, onMounted } from 'vue';
import { useUIStore } from '../../stores/ui';
import SidebarLink from './SidebarLink.vue';
import {
  HomeIcon,
  UsersIcon,
  WrenchScrewdriverIcon,
  BuildingOfficeIcon,
  TagIcon,
  MapPinIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  BanknotesIcon,
  CreditCardIcon,
  PhotoIcon,
  FlagIcon,
  CogIcon,
  MoonIcon,
  SunIcon,
  GlobeAltIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const uiStore = useUIStore();

const sidebarCollapsed = computed(() => uiStore.sidebarCollapsed);
const sidebarMobileOpen = computed(() => uiStore.sidebarMobileOpen);
const isDark = computed(() => uiStore.isDark);
const locale = computed(() => uiStore.locale);

const pendingCount = ref(0);
const reportsCount = ref(0);

async function fetchCounts() {
  try {
    const res = await axios.get('/api/admin/dashboard/stats');
    pendingCount.value = res.data.stats?.pending_vacancies || 0;
  } catch {
    // ignore
  }
}

onMounted(fetchCounts);

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
