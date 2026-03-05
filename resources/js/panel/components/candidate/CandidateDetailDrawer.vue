<template>
  <AppDrawer
    :open="open"
    title="Kandidat ma'lumotlari"
    size="xl"
    @close="$emit('close')"
  >
    <div v-if="candidate">
      <!-- Header -->
      <div class="flex items-start gap-4 pb-6 border-b border-surface-200 dark:border-surface-700">
        <AppAvatar
          :name="candidate.name"
          :src="candidate.avatar"
          size="xl"
          color="brand"
          :status="candidate.online ? 'online' : 'offline'"
        />
        <div class="flex-1">
          <h2 class="text-xl font-bold text-surface-900 dark:text-surface-100 mb-1">
            {{ candidate.name }}
          </h2>
          <div class="flex items-center gap-2 mb-2">
            <AppBadge :variant="getScoreVariant(candidate.score)">
              {{ candidate.score }} ball
            </AppBadge>
            <AppBadge variant="default">
              {{ getStageLabel(candidate.stage) }}
            </AppBadge>
          </div>
          <div class="flex items-center gap-4 text-sm text-surface-600 dark:text-surface-400">
            <div class="flex items-center gap-1.5">
              <PhoneIcon class="h-4 w-4" />
              <span>{{ candidate.phone }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <CalendarIcon class="h-4 w-4" />
              <span>{{ formatDate(candidate.applied_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mt-6">
        <AppTabs v-model="activeTab" :tabs="tabs" variant="pills">
          <!-- Profile Tab -->
          <template #panel-profile>
            <div class="space-y-6">
              <!-- Contact Info -->
              <div>
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100 mb-3">
                  Kontakt ma'lumotlari
                </h3>
                <div class="space-y-2">
                  <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-surface-600 dark:text-surface-400">Telefon</span>
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ candidate.phone }}
                    </span>
                  </div>
                  <div v-if="candidate.email" class="flex items-center justify-between py-2">
                    <span class="text-sm text-surface-600 dark:text-surface-400">Email</span>
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ candidate.email }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-surface-600 dark:text-surface-400">Telegram</span>
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      @{{ candidate.telegram_username }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Personal Info -->
              <div>
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100 mb-3">
                  Shaxsiy ma'lumotlar
                </h3>
                <div class="space-y-2">
                  <div v-if="candidate.age" class="flex items-center justify-between py-2">
                    <span class="text-sm text-surface-600 dark:text-surface-400">Yoshi</span>
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ candidate.age }} yosh
                    </span>
                  </div>
                  <div v-if="candidate.location" class="flex items-center justify-between py-2">
                    <span class="text-sm text-surface-600 dark:text-surface-400">Manzil</span>
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ candidate.location }}
                    </span>
                  </div>
                  <div v-if="candidate.education" class="flex items-center justify-between py-2">
                    <span class="text-sm text-surface-600 dark:text-surface-400">Ma'lumoti</span>
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ candidate.education }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Source -->
              <div>
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100 mb-3">
                  Manba
                </h3>
                <div class="flex items-center gap-2">
                  <span class="text-xl">{{ getSourceIcon(candidate.source) }}</span>
                  <span class="text-sm text-surface-700 dark:text-surface-300">
                    {{ getSourceLabel(candidate.source) }}
                  </span>
                </div>
              </div>
            </div>
          </template>

          <!-- Responses Tab -->
          <template #panel-responses>
            <div class="space-y-4">
              <div
                v-for="(response, index) in candidate.responses"
                :key="response.id"
                class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg"
              >
                <div class="flex items-start justify-between mb-2">
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="text-xs font-medium text-surface-500 dark:text-surface-400">
                        Savol {{ index + 1 }}
                      </span>
                      <AppBadge size="sm" variant="primary">
                        {{ response.type }}
                      </AppBadge>
                    </div>
                    <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ response.question }}
                    </p>
                  </div>
                  <div class="text-right ml-4">
                    <AppBadge :variant="response.score > 0 ? 'success' : 'default'" size="sm">
                      {{ response.score }}/{{ response.max_score }}
                    </AppBadge>
                  </div>
                </div>

                <div class="mt-2 pl-4 border-l-2 border-surface-200 dark:border-surface-700">
                  <p class="text-sm text-surface-700 dark:text-surface-300">
                    {{ response.answer }}
                  </p>
                </div>
              </div>
            </div>
          </template>

          <!-- Timeline Tab -->
          <template #panel-timeline>
            <div class="space-y-4">
              <div
                v-for="event in candidate.timeline"
                :key="event.id"
                class="flex gap-4"
              >
                <div class="flex flex-col items-center">
                  <div :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center',
                    getEventColor(event.type),
                  ]">
                    <component :is="getEventIcon(event.type)" class="h-5 w-5 text-white" />
                  </div>
                  <div class="w-0.5 h-full bg-surface-200 dark:bg-surface-700 mt-2" />
                </div>

                <div class="flex-1 pb-6">
                  <div class="flex items-start justify-between mb-1">
                    <p class="text-sm font-medium text-surface-900 dark:text-surface-100">
                      {{ event.title }}
                    </p>
                    <span class="text-xs text-surface-500 dark:text-surface-400">
                      {{ formatTime(event.created_at) }}
                    </span>
                  </div>
                  <p v-if="event.description" class="text-sm text-surface-600 dark:text-surface-400">
                    {{ event.description }}
                  </p>
                </div>
              </div>
            </div>
          </template>
        </AppTabs>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3">
        <AppButton variant="primary" full-width>
          <template #icon-left>
            <ChatBubbleLeftIcon class="h-5 w-5" />
          </template>
          Xabar yuborish
        </AppButton>
        <AppButton variant="outline" full-width>
          <template #icon-left>
            <ArrowPathIcon class="h-5 w-5" />
          </template>
          Bosqichni o'zgartirish
        </AppButton>
      </div>
    </template>
  </AppDrawer>
</template>

<script setup>
import { ref } from 'vue';
import {
  PhoneIcon,
  CalendarIcon,
  ChatBubbleLeftIcon,
  ArrowPathIcon,
  UserPlusIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline';
import AppDrawer from '../ui/AppDrawer.vue';
import AppAvatar from '../ui/AppAvatar.vue';
import AppBadge from '../ui/AppBadge.vue';
import AppTabs from '../ui/AppTabs.vue';
import AppButton from '../ui/AppButton.vue';

defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  candidate: {
    type: Object,
    default: null,
  },
});

defineEmits(['close']);

const activeTab = ref('profile');

const tabs = [
  { key: 'profile', label: 'Profil' },
  { key: 'responses', label: 'Javoblar' },
  { key: 'timeline', label: 'Tarix' },
];

function getScoreVariant(score) {
  if (score >= 9) return 'success';
  if (score >= 7) return 'primary';
  if (score >= 5) return 'warning';
  return 'danger';
}

function getStageLabel(stage) {
  const labels = {
    new: 'Yangi',
    screening: 'Saralash',
    interview: 'Intervyu',
    offer: 'Taklif',
    hired: 'Yollandi',
    rejected: 'Rad etildi',
  };
  return labels[stage] || stage;
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
    telegram: 'Telegram Bot',
    direct: 'To\'g\'ridan-to\'g\'ri havola',
    referral: 'Tavsiya',
  };
  return labels[source] || source;
}

function getEventColor(type) {
  const colors = {
    applied: 'bg-brand-500',
    stage_changed: 'bg-info-500',
    note_added: 'bg-warning-500',
    accepted: 'bg-success-500',
    rejected: 'bg-danger-500',
  };
  return colors[type] || 'bg-surface-500';
}

function getEventIcon(type) {
  const icons = {
    applied: UserPlusIcon,
    stage_changed: ArrowPathIcon,
    note_added: ChatBubbleLeftIcon,
    accepted: CheckCircleIcon,
    rejected: XCircleIcon,
  };
  return icons[type] || ClockIcon;
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
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
