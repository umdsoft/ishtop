<template>
  <div class="overflow-hidden rounded-lg border border-surface-200 dark:border-surface-700">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
        <thead class="bg-surface-50 dark:bg-surface-900">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              :class="[
                'px-4 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider',
                column.sortable ? 'cursor-pointer select-none hover:bg-surface-100 dark:hover:bg-surface-800' : '',
              ]"
              @click="column.sortable ? handleSort(column.key) : null"
            >
              <div class="flex items-center gap-2">
                <span>{{ column.label }}</span>
                <span v-if="column.sortable" class="flex flex-col">
                  <ChevronUpIcon
                    :class="[
                      'h-3 w-3 -mb-1',
                      sortBy === column.key && sortOrder === 'asc' ? 'text-brand-600' : 'text-surface-300 dark:text-surface-600',
                    ]"
                  />
                  <ChevronDownIcon
                    :class="[
                      'h-3 w-3',
                      sortBy === column.key && sortOrder === 'desc' ? 'text-brand-600' : 'text-surface-300 dark:text-surface-600',
                    ]"
                  />
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-surface-950 divide-y divide-surface-200 dark:divide-surface-700">
          <tr
            v-for="(row, rowIndex) in sortedData"
            :key="rowIndex"
            :class="[
              'transition-colors duration-150',
              hoverable ? 'hover:bg-surface-50 dark:hover:bg-surface-900' : '',
              clickable ? 'cursor-pointer' : '',
            ]"
            @click="clickable ? $emit('row-click', row) : null"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-4 py-3 whitespace-nowrap text-sm text-surface-900 dark:text-surface-100"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>

          <tr v-if="data.length === 0">
            <td :colspan="columns.length" class="px-4 py-12 text-center text-surface-500 dark:text-surface-400">
              <slot name="empty">
                <div class="flex flex-col items-center gap-2">
                  <svg class="h-12 w-12 text-surface-300 dark:text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="text-sm">Ma'lumot topilmadi</p>
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
    // Expected format: [{ key: 'name', label: 'Name', sortable: true }, ...]
  },
  data: {
    type: Array,
    required: true,
  },
  hoverable: {
    type: Boolean,
    default: true,
  },
  clickable: {
    type: Boolean,
    default: false,
  },
  defaultSortBy: {
    type: String,
    default: null,
  },
  defaultSortOrder: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value),
  },
});

defineEmits(['row-click']);

const sortBy = ref(props.defaultSortBy);
const sortOrder = ref(props.defaultSortOrder);

const sortedData = computed(() => {
  if (!sortBy.value) {
    return props.data;
  }

  return [...props.data].sort((a, b) => {
    const aVal = a[sortBy.value];
    const bVal = b[sortBy.value];

    if (aVal === bVal) return 0;

    const comparison = aVal < bVal ? -1 : 1;
    return sortOrder.value === 'asc' ? comparison : -comparison;
  });
});

function handleSort(key) {
  if (sortBy.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = key;
    sortOrder.value = 'asc';
  }
}
</script>
