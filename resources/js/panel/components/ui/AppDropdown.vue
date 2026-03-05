<template>
  <Menu as="div" class="relative inline-block text-left">
    <MenuButton :class="buttonClass">
      <slot name="trigger">
        <AppButton :variant="variant" :size="size">
          {{ label }}
          <template #icon-right>
            <ChevronDownIcon class="h-4 w-4" />
          </template>
        </AppButton>
      </slot>
    </MenuButton>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <MenuItems
        :class="[
          'absolute z-50 mt-2 rounded-lg bg-white dark:bg-surface-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none',
          widthClasses,
          alignmentClasses,
        ]"
      >
        <div class="py-1">
          <MenuItem
            v-for="(item, index) in items"
            :key="index"
            v-slot="{ active, close }"
            :disabled="item.disabled"
          >
            <component
              :is="item.href ? 'a' : 'button'"
              :href="item.href"
              :class="[
                active ? 'bg-surface-50 dark:bg-surface-700 text-surface-900 dark:text-surface-100' : 'text-surface-700 dark:text-surface-300',
                item.disabled ? 'opacity-50 cursor-not-allowed' : '',
                item.danger ? 'text-danger-600 dark:text-danger-400' : '',
                'group flex items-center w-full px-4 py-2 text-sm transition-colors',
              ]"
              @click="item.onClick ? handleItemClick(item, close) : null"
            >
              <component v-if="item.icon" :is="item.icon" class="mr-3 h-5 w-5" />
              <span class="flex-1">{{ item.label }}</span>
              <AppBadge v-if="item.badge" variant="primary" size="sm" class="ml-2">
                {{ item.badge }}
              </AppBadge>
            </component>
          </MenuItem>

          <div v-if="$slots.default" class="border-t border-surface-200 dark:border-surface-700 mt-1 pt-1">
            <slot />
          </div>
        </div>
      </MenuItems>
    </transition>
  </Menu>
</template>

<script setup>
import { computed } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import AppButton from './AppButton.vue';
import AppBadge from './AppBadge.vue';

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
    // Expected format: [{ label: 'Item', icon: IconComponent, onClick: fn, href: '', disabled: false, danger: false, badge: 3 }, ...]
  },
  label: {
    type: String,
    default: 'Options',
  },
  variant: {
    type: String,
    default: 'secondary',
  },
  size: {
    type: String,
    default: 'md',
  },
  align: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'right'].includes(value),
  },
  width: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'auto'].includes(value),
  },
  buttonClass: {
    type: String,
    default: '',
  },
});

const widthClasses = computed(() => {
  const widths = {
    sm: 'w-48',
    md: 'w-56',
    lg: 'w-64',
    auto: 'w-auto min-w-48',
  };
  return widths[props.width];
});

const alignmentClasses = computed(() => {
  return props.align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left';
});

function handleItemClick(item, close) {
  if (item.onClick && !item.disabled) {
    item.onClick();
    close();
  }
}
</script>
