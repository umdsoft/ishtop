<template>
  <router-link
    :to="to"
    :class="[
      'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group relative',
      isActive
        ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-400 border-l-4 border-brand-500 -ml-[13px] pl-[9px]'
        : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 hover:text-surface-900 dark:hover:text-surface-100',
    ]"
  >
    <component
      :is="icon"
      :class="[
        'w-5 h-5 flex-shrink-0 transition-colors',
        isActive ? 'text-brand-600 dark:text-brand-400' : 'text-surface-500 dark:text-surface-500 group-hover:text-surface-700 dark:group-hover:text-surface-300'
      ]"
    />

    <span
      v-if="!collapsed"
      class="text-sm font-medium truncate"
    >
      {{ label }}
    </span>

    <span
      v-if="badge"
      :class="[
        'ml-auto px-2 py-0.5 text-xs font-semibold rounded-full',
        isActive
          ? 'bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300'
          : 'bg-surface-200 dark:bg-surface-700 text-surface-700 dark:text-surface-300'
      ]"
    >
      {{ badge }}
    </span>

    <!-- Tooltip for collapsed sidebar -->
    <transition name="fade">
      <div
        v-if="collapsed"
        class="absolute left-full ml-2 px-3 py-2 bg-surface-900 dark:bg-surface-800 text-surface-100 text-sm font-medium rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap z-50 transition-opacity duration-200"
      >
        {{ label }}
      </div>
    </transition>
  </router-link>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  to: {
    type: String,
    required: true,
  },
  icon: {
    type: Object,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  badge: {
    type: [String, Number],
    default: null,
  },
  collapsed: {
    type: Boolean,
    default: false,
  },
  exact: {
    type: Boolean,
    default: false,
  },
});

const route = useRoute();

const isActive = computed(() => {
  // Exact match for dashboard home to prevent it staying active on child routes
  if (props.to === '/dashboard' || props.exact) {
    return route.path === props.to;
  }
  return route.path === props.to || route.path.startsWith(props.to + '/');
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
