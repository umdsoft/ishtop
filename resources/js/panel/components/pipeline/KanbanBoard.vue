<template>
  <div class="overflow-x-auto">
    <div class="inline-flex gap-4 pb-4 min-w-full">
      <KanbanColumn
        v-for="stage in stages"
        :key="stage.key"
        :stage="stage"
        :candidates="getCandidatesByStage(stage.key)"
        @candidate-move="handleCandidateMove"
        @candidate-click="handleCandidateClick"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import KanbanColumn from './KanbanColumn.vue';

const props = defineProps({
  vacancyId: {
    type: [String, Number],
    required: true,
  },
});

const emit = defineEmits(['candidate-click', 'candidate-moved']);

// Pipeline stages
const stages = ref([
  {
    key: 'new',
    label: 'Yangi',
    color: 'bg-brand-500',
    icon: '📝',
  },
  {
    key: 'screening',
    label: 'Saralash',
    color: 'bg-info-500',
    icon: '🔍',
  },
  {
    key: 'interview',
    label: 'Intervyu',
    color: 'bg-warning-500',
    icon: '💬',
  },
  {
    key: 'offer',
    label: 'Taklif',
    color: 'bg-success-500',
    icon: '📄',
  },
  {
    key: 'hired',
    label: 'Yollandi',
    color: 'bg-success-600',
    icon: '✅',
  },
  {
    key: 'rejected',
    label: 'Rad etildi',
    color: 'bg-danger-500',
    icon: '❌',
  },
]);

// Mock candidates data
const candidates = ref([
  {
    id: 1,
    name: 'Aziz Karimov',
    stage: 'new',
    score: 8.5,
    avatar: null,
    phone: '+998901234567',
    applied_at: '2024-03-04 14:30',
    source: 'telegram',
  },
  {
    id: 2,
    name: 'Madina Alieva',
    stage: 'new',
    score: 9.2,
    avatar: null,
    phone: '+998901234568',
    applied_at: '2024-03-04 13:15',
    source: 'telegram',
  },
  {
    id: 3,
    name: 'Jahongir Rahimov',
    stage: 'screening',
    score: 7.8,
    avatar: null,
    phone: '+998901234569',
    applied_at: '2024-03-03 16:45',
    source: 'direct',
  },
  {
    id: 4,
    name: 'Dilnoza Sadikova',
    stage: 'interview',
    score: 8.9,
    avatar: null,
    phone: '+998901234570',
    applied_at: '2024-03-02 10:20',
    source: 'telegram',
  },
  {
    id: 5,
    name: 'Bobur Tursunov',
    stage: 'offer',
    score: 9.5,
    avatar: null,
    phone: '+998901234571',
    applied_at: '2024-03-01 09:00',
    source: 'telegram',
  },
]);

function getCandidatesByStage(stageKey) {
  return candidates.value.filter(c => c.stage === stageKey);
}

function handleCandidateMove({ candidateId, fromStage, toStage }) {
  const candidate = candidates.value.find(c => c.id === candidateId);
  if (candidate) {
    candidate.stage = toStage;
    emit('candidate-moved', { candidateId, fromStage, toStage });
  }
}

function handleCandidateClick(candidate) {
  emit('candidate-click', candidate);
}
</script>
