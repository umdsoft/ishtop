<template>
  <div>
    <SearchHero :stats="data?.stats" :regions="data?.regions" :loading="loading" />
    <CategoryGrid :categories="data?.categories" :loading="loading" />
    <VacancySectionGrid
      v-if="loading || data?.topVacancies?.length"
      :title="$t('top_vacancies')"
      :vacancies="data?.topVacancies"
      :loading="loading"
      badge="top"
      :showViewAll="true"
    />
    <VacancySectionGrid
      v-if="loading || data?.urgentVacancies?.length"
      :title="$t('urgent_vacancies')"
      :vacancies="data?.urgentVacancies"
      :loading="loading"
      badge="urgent"
      :showViewAll="true"
    />
    <VacancySectionGrid
      v-if="loading || data?.latestVacancies?.length"
      :title="$t('latest_vacancies')"
      :vacancies="data?.latestVacancies"
      :loading="loading"
      :showViewAll="true"
    />
    <RegionGrid :regions="data?.regions" :loading="loading" />
    <WhyKadrgo />
    <TelegramCta />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '@website/composables/useApi';
import { useSeo } from '@website/composables/useSeo';
import SearchHero from '@website/components/SearchHero.vue';
import CategoryGrid from '@website/components/CategoryGrid.vue';
import VacancySectionGrid from '@website/components/VacancySectionGrid.vue';
import RegionGrid from '@website/components/RegionGrid.vue';
import WhyKadrgo from '@website/components/WhyKadrgo.vue';
import TelegramCta from '@website/components/TelegramCta.vue';

const { getHome } = useApi();
const seo = useSeo();

const data = ref(null);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await getHome();
    data.value = res.data;
    seo.set({
      title: 'KadrGo — O\'zbekistondagi eng yirik ish qidirish platformasi',
      description: 'Minglab vakansiyalar, ishonchli kompaniyalar. Telegramdan chiqmasdan ish toping.',
    });
  } catch (e) {
    console.error('Home data fetch error:', e);
  } finally {
    loading.value = false;
  }
});
</script>
