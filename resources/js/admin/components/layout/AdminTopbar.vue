<template>
  <header class="sticky top-0 z-40 bg-surface-0/80 dark:bg-surface-900/80 backdrop-blur-lg border-b border-surface-200 dark:border-surface-800">
    <div class="flex items-center justify-between px-4 md:px-6 py-3">
      <!-- Left -->
      <div class="flex items-center gap-3">
        <button
          class="lg:hidden p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
          @click="toggleMobileSidebar"
        >
          <Bars3Icon class="w-6 h-6" />
        </button>

        <button
          class="hidden lg:block p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
          @click="toggleSidebar"
        >
          <Bars3Icon class="w-5 h-5" />
        </button>
      </div>

      <!-- Right -->
      <div class="flex items-center gap-2">
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

          <transition name="slide-down">
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-56 bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl shadow-xl overflow-hidden py-1"
            >
              <div class="px-4 py-3 border-b border-surface-200 dark:border-surface-800">
                <p class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ user?.first_name }} {{ user?.last_name }}</p>
                <p class="text-xs text-surface-500 dark:text-surface-400">{{ user?.email || user?.username }}</p>
              </div>

              <button
                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/20 transition-colors"
                @click="handleLogout"
              >
                <ArrowRightOnRectangleIcon class="w-4 h-4" />
                {{ $t('auth.logout') }}
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
  ChevronDownIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const uiStore = useUIStore();
const authStore = useAuthStore();

const showUserMenu = ref(false);

const user = computed(() => authStore.user);
const userInitials = computed(() => {
  if (!user.value) return 'A';
  const first = user.value.first_name?.[0] || '';
  const last = user.value.last_name?.[0] || '';
  return (first + last).toUpperCase() || 'A';
});

function toggleSidebar() {
  uiStore.toggleSidebar();
}

function toggleMobileSidebar() {
  uiStore.toggleMobileSidebar();
}

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value;
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
