<template>
  <div v-if="banner" class="banner-container mb-4">
    <div v-if="banner.type === 'hero'" class="relative rounded-xl overflow-hidden">
      <img
        :src="banner.image_url"
        :alt="banner.title"
        class="w-full h-48 object-cover"
        @click="handleClick"
      />
      <div v-if="banner.advertiser_name" class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
        Reklama
      </div>
    </div>

    <div v-else-if="banner.type === 'card'" class="card cursor-pointer" @click="handleClick">
      <div class="flex gap-3">
        <img
          v-if="banner.image_url"
          :src="banner.image_url"
          :alt="banner.title"
          class="w-20 h-20 object-cover rounded-lg"
        />
        <div class="flex-1">
          <h3 class="font-semibold text-sm mb-1">{{ banner.title }}</h3>
          <span class="text-xs text-tg-hint">Reklama</span>
        </div>
      </div>
    </div>

    <div v-else-if="banner.type === 'inline'" class="flex items-center justify-between p-3 bg-blue-50 rounded-lg cursor-pointer" @click="handleClick">
      <span class="text-sm font-medium">{{ banner.title }}</span>
      <span class="text-xs text-tg-hint">Reklama</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/api'
import { useTelegram } from '@/composables/useTelegram'

const props = defineProps({
  placement: {
    type: String,
    required: true,
  },
})

const banner = ref(null)
const telegram = useTelegram()

onMounted(async () => {
  try {
    const response = await api.get('/banners', {
      params: { placement: props.placement },
    })
    if (response.data.banners && response.data.banners.length > 0) {
      banner.value = response.data.banners[0]

      // Track impression
      if (banner.value) {
        await api.post(`/banners/${banner.value.id}/impression`)
      }
    }
  } catch (error) {
    console.error('Failed to load banner:', error)
  }
})

async function handleClick() {
  if (!banner.value) return

  telegram.hapticFeedback('medium')

  try {
    const response = await api.post(`/banners/${banner.value.id}/click`)

    if (response.data.url) {
      telegram.openLink(response.data.url)
    }
  } catch (error) {
    console.error('Failed to track banner click:', error)
  }
}
</script>
