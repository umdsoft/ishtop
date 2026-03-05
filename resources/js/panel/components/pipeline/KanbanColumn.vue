<template>
  <div class="flex-shrink-0 w-80">
    <div class="bg-surface-50 dark:bg-surface-900 rounded-lg p-4">
      <!-- Column Header -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <span class="text-xl">{{ stage.icon }}</span>
          <h3 class="font-semibold text-surface-900 dark:text-surface-100">
            {{ stage.label }}
          </h3>
          <AppBadge variant="default" size="sm">
            {{ candidates.length }}
          </AppBadge>
        </div>
      </div>

      <!-- Drop Zone -->
      <draggable
        v-model="localCandidates"
        :group="{ name: 'candidates', pull: true, put: true }"
        :animation="200"
        item-key="id"
        class="space-y-3 min-h-[200px]"
        ghost-class="opacity-50"
        @change="handleDragChange"
      >
        <template #item="{ element }">
          <KanbanCard
            :candidate="element"
            :stage-color="stage.color"
            @click="$emit('candidate-click', element)"
          />
        </template>
      </draggable>

      <!-- Empty State -->
      <div v-if="localCandidates.length === 0" class="py-8 text-center">
        <p class="text-sm text-surface-500 dark:text-surface-400">
          Kandidatlar yo'q
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';
import AppBadge from '../ui/AppBadge.vue';
import KanbanCard from './KanbanCard.vue';

const props = defineProps({
  stage: {
    type: Object,
    required: true,
  },
  candidates: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['candidate-move', 'candidate-click']);

const localCandidates = ref([...props.candidates]);

// Sync local candidates with props
watch(
  () => props.candidates,
  (newCandidates) => {
    localCandidates.value = [...newCandidates];
  },
  { deep: true }
);

function handleDragChange(event) {
  if (event.added) {
    // Candidate moved to this column
    const candidate = event.added.element;
    emit('candidate-move', {
      candidateId: candidate.id,
      fromStage: candidate.stage,
      toStage: props.stage.key,
    });
  }
}
</script>
