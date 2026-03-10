<template>
  <div class="min-h-screen flex flex-col">
    <TheNavbar />
    <main class="flex-1">
      <router-view />
    </main>
    <TheFooter />
  </div>
</template>

<script setup>
import TheNavbar from '@website/components/TheNavbar.vue';
import TheFooter from '@website/components/TheFooter.vue';
</script>
