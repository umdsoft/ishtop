<template>
  <router-link
    :to="to"
    custom
    v-slot="{ isActive, navigate }"
  >
    <button
      @click="navigate"
      :class="[
        'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-left',
        isActive
          ? 'bg-brand-50 dark:bg-brand-950/30 text-brand-600 dark:text-brand-400'
          : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800',
        collapsed ? 'justify-center' : '',
      ]"
      :title="collapsed ? label : undefined"
    >
      <component :is="icon" class="w-5 h-5 shrink-0" />
      <span v-if="!collapsed" class="text-sm font-medium truncate flex-1">{{ label }}</span>
      <span
        v-if="!collapsed && badge > 0"
        :class="[
          'text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center',
          badgeColor === 'danger'
            ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-400'
            : 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-400',
        ]"
      >
        {{ badge }}
      </span>
    </button>
  </router-link>
</template>

<script setup>
defineProps({
  to: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
  label: { type: String, required: true },
  collapsed: { type: Boolean, default: false },
  exact: { type: Boolean, default: false },
  badge: { type: Number, default: 0 },
  badgeColor: { type: String, default: 'warning' },
});
</script>
