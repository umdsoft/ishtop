<template>
  <div
    class="bg-white dark:bg-surface-800 rounded-lg p-4 border border-surface-200 dark:border-surface-700 hover:shadow-lg transition-all duration-200 cursor-pointer group"
    @click="$emit('click', candidate)"
  >
    <!-- Header -->
    <div class="flex items-start justify-between mb-3">
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <AppAvatar
          :name="candidate.name"
          :src="candidate.avatar"
          size="md"
          color="brand"
        />
        <div class="flex-1 min-w-0">
          <h4 class="font-semibold text-surface-900 dark:text-surface-100 truncate">
            {{ candidate.name }}
          </h4>
          <p class="text-xs text-surface-500 dark:text-surface-400">
            {{ formatTime(candidate.applied_at) }}
          </p>
        </div>
      </div>

      <!-- Score Badge -->
      <AppBadge
        v-if="candidate.score"
        :variant="getScoreVariant(candidate.score)"
        size="sm"
      >
        {{ candidate.score }}
      </AppBadge>
    </div>

    <!-- Contact -->
    <div class="flex items-center gap-2 text-sm text-surface-600 dark:text-surface-400 mb-3">
      <PhoneIcon class="h-4 w-4 flex-shrink-0" />
      <span class="truncate">{{ candidate.phone }}</span>
    </div>

    <!-- Footer -->
    <div class="flex items-center justify-between">
      <!-- Source -->
      <div class="flex items-center gap-1.5">
        <span class="text-xs">{{ getSourceIcon(candidate.source) }}</span>
        <span class="text-xs text-surface-500 dark:text-surface-400">
          {{ getSourceLabel(candidate.source) }}
        </span>
      </div>

      <!-- Quick Actions (on hover) -->
      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button
          class="p-1 rounded hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-600 dark:text-surface-400"
          title="Ko'rish"
          @click.stop="$emit('view', candidate)"
        >
          <EyeIcon class="h-4 w-4" />
        </button>
        <button
          class="p-1 rounded hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-600 dark:text-surface-400"
          title="Izoh qo'shish"
          @click.stop="$emit('add-note', candidate)"
        >
          <ChatBubbleLeftIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Tags (if any) -->
    <div v-if="candidate.tags && candidate.tags.length > 0" class="flex flex-wrap gap-1 mt-3">
      <AppBadge
        v-for="tag in candidate.tags"
        :key="tag"
        variant="default"
        size="sm"
        rounded="md"
      >
        {{ tag }}
      </AppBadge>
    </div>
  </div>
</template>

<script setup>
import { PhoneIcon, EyeIcon, ChatBubbleLeftIcon } from '@heroicons/vue/24/outline';
import AppAvatar from '../ui/AppAvatar.vue';
import AppBadge from '../ui/AppBadge.vue';

defineProps({
  candidate: {
    type: Object,
    required: true,
  },
  stageColor: {
    type: String,
    default: 'bg-brand-500',
  },
});

defineEmits(['click', 'view', 'add-note']);

function getScoreVariant(score) {
  if (score >= 9) return 'success';
  if (score >= 7) return 'primary';
  if (score >= 5) return 'warning';
  return 'danger';
}

function getSourceIcon(source) {
  const icons = {
    telegram: '📱',
    direct: '🔗',
    referral: '👥',
  };
  return icons[source] || '📄';
}

function getSourceLabel(source) {
  const labels = {
    telegram: 'Telegram',
    direct: 'To\'g\'ridan-to\'g\'ri',
    referral: 'Tavsiya',
  };
  return labels[source] || source;
}

function formatTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 60) {
    return `${diffMins} daqiqa oldin`;
  } else if (diffHours < 24) {
    return `${diffHours} soat oldin`;
  } else if (diffDays < 7) {
    return `${diffDays} kun oldin`;
  } else {
    return date.toLocaleDateString('uz-UZ', { month: 'short', day: 'numeric' });
  }
}
</script>
