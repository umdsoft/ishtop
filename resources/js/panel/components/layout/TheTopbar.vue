<template>
  <header class="sticky top-0 z-40 bg-surface-0/80 dark:bg-surface-900/80 backdrop-blur-lg border-b border-surface-200 dark:border-surface-800">
    <div class="flex items-center justify-between px-4 md:px-6 py-3">
      <!-- Left: Hamburger + Search -->
      <div class="flex items-center gap-3">
        <!-- Mobile hamburger -->
        <button
          class="lg:hidden p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
          @click="toggleMobileSidebar"
        >
          <Bars3Icon class="w-6 h-6" />
        </button>

        <!-- Desktop sidebar toggle -->
        <button
          class="hidden lg:block p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
          @click="toggleSidebar"
        >
          <Bars3Icon class="w-5 h-5" />
        </button>

        <!-- Global search (Command Palette trigger) -->
        <button
          class="hidden md:flex items-center gap-2 px-3 py-2 bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 dark:hover:bg-surface-700 rounded-lg transition-colors text-surface-500 dark:text-surface-400 text-sm"
          @click="openCommandPalette"
        >
          <MagnifyingGlassIcon class="w-4 h-4" />
          <span>Qidirish...</span>
          <kbd class="hidden lg:inline-block ml-4 px-1.5 py-0.5 text-xs font-mono bg-surface-200 dark:bg-surface-700 rounded">
            ⌘K
          </kbd>
        </button>
      </div>

      <!-- Right: Notifications + User Menu -->
      <div class="flex items-center gap-2">
        <!-- Notifications -->
        <div class="relative">
          <button
            class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors relative"
            @click="toggleNotifications"
          >
            <BellIcon class="w-5 h-5 text-surface-600 dark:text-surface-400" />
            <span
              v-if="unreadCount > 0"
              class="absolute top-1 right-1 w-2 h-2 bg-danger-500 rounded-full animate-pulse-soft"
            />
          </button>

          <!-- Notification dropdown (placeholder) -->
          <transition name="slide-down">
            <div
              v-if="showNotifications"
              class="absolute right-0 mt-2 w-80 bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl shadow-xl overflow-hidden"
            >
              <div class="p-4 border-b border-surface-200 dark:border-surface-800">
                <h3 class="font-semibold text-surface-900 dark:text-surface-100">Bildirishnomalar</h3>
              </div>
              <div class="p-4 text-center text-surface-500 dark:text-surface-400 text-sm">
                Bildirishnomalar yo'q
              </div>
            </div>
          </transition>
        </div>

        <!-- User menu -->
        <div class="relative">
          <button
            class="flex items-center gap-2 p-1.5 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
            @click="toggleUserMenu"
          >
            <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-600 dark:text-brand-400 flex items-center justify-center font-semibold text-sm">
              {{ userInitials }}
            </div>
            <span class="hidden md:block text-sm font-medium text-surface-700 dark:text-surface-300 max-w-[150px] truncate">
              {{ user?.first_name }} {{ user?.last_name }}
            </span>
            <ChevronDownIcon class="w-4 h-4 text-surface-500 dark:text-surface-400 hidden md:block" />
          </button>

          <!-- User dropdown -->
          <transition name="slide-down">
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-56 bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl shadow-xl overflow-hidden py-1"
            >
              <div class="px-4 py-3 border-b border-surface-200 dark:border-surface-800">
                <p class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ user?.first_name }} {{ user?.last_name }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400">{{ user?.email }}</p>
                <p v-if="activeCompany" class="text-xs text-brand-600 dark:text-brand-400 mt-0.5">
                  {{ activeCompany.company_name }}
                </p>
              </div>

              <router-link
                to="/dashboard/settings/profile"
                class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
                @click="showUserMenu = false"
              >
                <UserCircleIcon class="w-4 h-4" />
                Profil
              </router-link>

              <router-link
                to="/dashboard/settings/company"
                class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
                @click="showUserMenu = false"
              >
                <BuildingOfficeIcon class="w-4 h-4" />
                Kompaniya
              </router-link>

              <router-link
                to="/dashboard/settings/billing"
                class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
                @click="showUserMenu = false"
              >
                <CreditCardIcon class="w-4 h-4" />
                Obuna
              </router-link>

              <hr class="my-1 border-surface-200 dark:border-surface-800" />

              <button
                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/20 transition-colors"
                @click="handleLogout"
              >
                <ArrowRightOnRectangleIcon class="w-4 h-4" />
                Chiqish
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useUIStore } from '../../stores/ui';
import { useAuthStore } from '../../stores/auth';
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  BellIcon,
  ChevronDownIcon,
  UserCircleIcon,
  BuildingOfficeIcon,
  CreditCardIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const uiStore = useUIStore();
const authStore = useAuthStore();

const showNotifications = ref(false);
const showUserMenu = ref(false);
const unreadCount = ref(0); // TODO: Connect to real-time notifications

const user = computed(() => authStore.user);
const activeCompany = computed(() => authStore.activeCompany);
const userInitials = computed(() => {
  if (!user.value) return 'U';
  const first = user.value.first_name?.[0] || '';
  const last = user.value.last_name?.[0] || '';
  return (first + last).toUpperCase() || 'U';
});

function toggleSidebar() {
  uiStore.toggleSidebar();
}

function toggleMobileSidebar() {
  uiStore.toggleMobileSidebar();
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value;
  showUserMenu.value = false;
}

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value;
  showNotifications.value = false;
}

function openCommandPalette() {
  // TODO: Implement command palette
}

function handleLogout() {
  authStore.logout();
  router.push('/auth/login');
}
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.2s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
