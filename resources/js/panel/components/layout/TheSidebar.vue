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
        <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center flex-shrink-0">
          <svg width="20" height="20" viewBox="0 0 48 48" fill="none">
            <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
          </svg>
        </div>
        <div
          v-if="!sidebarCollapsed"
          class="flex flex-col leading-none transition-opacity duration-200"
        >
          <span class="text-[13px] font-extrabold tracking-[0.5px] text-brand-500">KADR</span>
          <span class="text-[13px] font-black tracking-[1px] text-accent-500 flex items-center">GO<span class="w-[5px] h-[5px] rounded-full bg-accent-500 ml-1"></span></span>
        </div>
      </router-link>

      <!-- Mobile close button -->
      <button
        class="lg:hidden p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
        @click="closeMobileSidebar"
      >
        <XMarkIcon class="w-5 h-5" />
      </button>
    </div>

    <!-- Company Switcher -->
    <div v-if="!sidebarCollapsed" class="px-3 py-3 border-b border-surface-200 dark:border-surface-800">
      <div class="relative company-switcher">
        <button
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg bg-surface-50 dark:bg-surface-800 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors text-left"
          @click="showCompanySwitcher = !showCompanySwitcher"
        >
          <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 text-sm font-bold shrink-0">
            {{ activeCompanyInitial }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-surface-900 dark:text-surface-100 truncate">
              {{ activeCompany?.company_name || 'Kompaniya tanlang' }}
            </p>
            <p v-if="activeCompany?.industry" class="text-xs text-surface-500 dark:text-surface-400 truncate">
              {{ activeCompany.industry }}
            </p>
          </div>
          <ChevronUpDownIcon class="w-4 h-4 text-surface-400 shrink-0" />
        </button>

        <!-- Dropdown -->
        <div
          v-if="showCompanySwitcher"
          class="absolute left-0 right-0 top-full mt-1 bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-lg shadow-xl z-50 py-1 max-h-64 overflow-y-auto"
        >
          <button
            v-for="company in companies"
            :key="company.id"
            class="w-full flex items-center gap-3 px-3 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors text-left"
            :class="{ 'bg-brand-50 dark:bg-brand-950/20': company.id === activeCompany?.id }"
            @click="handleSwitchCompany(company.id)"
          >
            <div class="w-6 h-6 rounded bg-surface-200 dark:bg-surface-700 flex items-center justify-center text-xs font-bold shrink-0">
              {{ company.company_name?.[0]?.toUpperCase() || 'K' }}
            </div>
            <span class="text-sm text-surface-700 dark:text-surface-300 truncate flex-1">
              {{ company.company_name }}
            </span>
            <CheckIcon v-if="company.id === activeCompany?.id" class="w-4 h-4 text-brand-500 shrink-0" />
          </button>

          <hr class="my-1 border-surface-200 dark:border-surface-800" />

          <router-link
            to="/dashboard/settings/company"
            class="flex items-center gap-3 px-3 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors text-sm text-brand-600 dark:text-brand-400"
            @click="showCompanySwitcher = false"
          >
            <PlusIcon class="w-4 h-4" />
            Yangi kompaniya qo'shish
          </router-link>
        </div>
      </div>
    </div>

    <!-- Collapsed: company initial only -->
    <div v-if="sidebarCollapsed" class="px-3 py-3 border-b border-surface-200 dark:border-surface-800 flex justify-center">
      <div
        class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 text-sm font-bold cursor-pointer"
        :title="activeCompany?.company_name"
      >
        {{ activeCompanyInitial }}
      </div>
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

      <!-- Rekruting Section -->
      <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">
        Rekruting
      </div>

      <SidebarLink
        to="/dashboard/candidates"
        :icon="UsersIcon"
        :label="$t('sidebar.talentPool')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/questionnaires"
        :icon="ClipboardDocumentListIcon"
        :label="$t('sidebar.questionnaires')"
        :collapsed="sidebarCollapsed"
        exact
      />

      <SidebarLink
        to="/dashboard/questionnaires/templates"
        :icon="DocumentDuplicateIcon"
        :label="$t('sidebar.questionnaireTemplates')"
        :collapsed="sidebarCollapsed"
      />

      <SidebarLink
        to="/dashboard/messages/templates"
        :icon="ChatBubbleLeftRightIcon"
        :label="$t('sidebar.messageTemplates')"
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useUIStore } from '../../stores/ui';
import { useAuthStore } from '../../stores/auth';
import { toast } from 'vue-sonner';
import SidebarLink from './SidebarLink.vue';
import {
  HomeIcon,
  BriefcaseIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  DocumentDuplicateIcon,
  ChatBubbleLeftRightIcon,

  ChartBarIcon,
  UserCircleIcon,
  BuildingOfficeIcon,
  CreditCardIcon,
  MoonIcon,
  SunIcon,
  GlobeAltIcon,
  XMarkIcon,
  CheckIcon,
  ChevronUpDownIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline';

const uiStore = useUIStore();
const authStore = useAuthStore();

const sidebarCollapsed = computed(() => uiStore.sidebarCollapsed);
const sidebarMobileOpen = computed(() => uiStore.sidebarMobileOpen);
const isDark = computed(() => uiStore.isDark);
const locale = computed(() => uiStore.locale);

// Company switcher
const showCompanySwitcher = ref(false);
const companies = computed(() => authStore.companies);
const activeCompany = computed(() => authStore.activeCompany);
const activeCompanyInitial = computed(() => {
  return activeCompany.value?.company_name?.[0]?.toUpperCase() || 'K';
});

async function handleSwitchCompany(companyId) {
  if (companyId === activeCompany.value?.id) {
    showCompanySwitcher.value = false;
    return;
  }
  const result = await authStore.switchCompany(companyId);
  showCompanySwitcher.value = false;
  if (result.success) {
    toast.success('Kompaniya almashtirildi');
  } else {
    toast.error(result.message);
  }
}

// Close dropdown on click outside
function handleClickOutside(event) {
  if (showCompanySwitcher.value && !event.target.closest('.company-switcher')) {
    showCompanySwitcher.value = false;
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

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
