<template>
  <div v-if="employer" class="bg-white rounded-xl p-5 border border-surface-100">
    <h3 class="text-sm font-semibold text-surface-500 uppercase tracking-wider mb-4">{{ $t('about_company') }}</h3>

    <div class="flex items-center gap-3 mb-4">
      <img
        v-if="employer.logo_url"
        :src="employer.logo_url"
        :alt="employer.company_name"
        class="w-12 h-12 rounded-xl object-cover"
      >
      <div v-else class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center">
        <span class="text-brand-600 font-bold text-lg">{{ employer.company_name?.[0] }}</span>
      </div>

      <div>
        <p class="font-semibold text-surface-900">{{ employer.company_name }}</p>
        <div class="flex items-center gap-2 mt-0.5">
          <span v-if="employer.verification_level" class="inline-flex items-center gap-1 text-xs text-green-600">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ $t('verified') }}
          </span>
          <span v-if="employer.rating" class="text-xs text-surface-500">
            {{ $t('rating') }}: {{ employer.rating }}/5 ({{ employer.rating_count }})
          </span>
        </div>
      </div>
    </div>

    <p v-if="employer.description" class="text-sm text-surface-600 leading-relaxed mb-4 line-clamp-3">
      {{ employer.description }}
    </p>

    <div v-if="employer.phone" class="flex items-center gap-2 text-sm">
      <svg class="w-4 h-4 text-surface-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
      </svg>
      <span class="text-surface-700 font-medium">{{ $t('contact_phone') }}:</span>
      <a :href="'tel:' + employer.phone" class="text-brand-500 hover:underline">{{ employer.phone }}</a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  employer: { type: Object, default: null },
});
</script>
