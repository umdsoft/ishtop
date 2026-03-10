<template>
  <div class="skeleton-card rounded-xl px-3.5 py-3" style="background-color: var(--tg-theme-secondary-bg-color);">
    <div class="flex items-center gap-3">
      <!-- Logo skeleton -->
      <div class="skeleton-pulse w-10 h-10 rounded-lg flex-shrink-0"></div>

      <div class="flex-1 min-w-0 space-y-2">
        <!-- Title -->
        <div class="skeleton-pulse h-3.5 rounded-full w-3/4"></div>
        <!-- Company + salary -->
        <div class="flex gap-2">
          <div class="skeleton-pulse h-3 rounded-full w-24"></div>
          <div class="skeleton-pulse h-3 rounded-full w-16"></div>
        </div>
        <!-- Meta -->
        <div class="flex gap-2">
          <div class="skeleton-pulse h-2.5 rounded-full w-20"></div>
          <div class="skeleton-pulse h-2.5 rounded-full w-14"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  count: { type: Number, default: 3 },
})
</script>

<style scoped>
.skeleton-pulse {
  background: linear-gradient(
    90deg,
    var(--tg-theme-secondary-bg-color) 0%,
    color-mix(in srgb, var(--tg-theme-hint-color) 15%, var(--tg-theme-secondary-bg-color)) 50%,
    var(--tg-theme-secondary-bg-color) 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
