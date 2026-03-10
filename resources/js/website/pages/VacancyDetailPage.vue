<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <PageSkeleton />
      <div class="bg-white rounded-xl p-6 space-y-4">
        <div class="h-6 bg-surface-100 rounded w-2/3 animate-pulse"></div>
        <div class="h-4 bg-surface-100 rounded w-1/2 animate-pulse"></div>
        <div class="h-32 bg-surface-100 rounded animate-pulse"></div>
      </div>
    </div>

    <template v-else-if="vacancy">
      <BreadcrumbNav :items="breadcrumbs" />

      <div class="mt-4 lg:flex lg:gap-6">
        <!-- Main content -->
        <div class="flex-1 min-w-0 space-y-4">
          <!-- Header card -->
          <div class="bg-white rounded-xl p-5 border border-surface-100">
            <div class="flex items-start gap-4">
              <img
                v-if="vacancy.employer?.logo_url"
                :src="vacancy.employer.logo_url"
                :alt="vacancy.employer.company_name"
                class="w-14 h-14 rounded-xl object-cover flex-shrink-0"
              >
              <div class="w-14 h-14 rounded-xl bg-brand-50 flex items-center justify-center flex-shrink-0" v-else>
                <span class="text-brand-600 font-bold text-lg">{{ vacancy.employer?.company_name?.[0] }}</span>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap gap-2 mb-2">
                  <span v-if="vacancy.is_top" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">
                    {{ $t('top') }}
                  </span>
                  <span v-if="vacancy.is_urgent" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-700">
                    {{ $t('urgent') }}
                  </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-surface-900">{{ vacancy.title }}</h1>

                <p class="text-brand-600 font-medium mt-1">{{ vacancy.employer?.company_name }}</p>

                <div class="flex flex-wrap items-center gap-3 mt-3 text-sm text-surface-500">
                  <span v-if="vacancy.city" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0115 0z" />
                    </svg>
                    {{ vacancy.city }}
                  </span>
                  <span v-if="vacancy.work_type" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    {{ $t(vacancy.work_type) }}
                  </span>
                </div>

                <!-- Salary -->
                <div class="mt-3">
                  <span v-if="vacancy.salary_min || vacancy.salary_max" class="text-lg font-bold text-brand-600">
                    <template v-if="vacancy.salary_min && vacancy.salary_max">
                      {{ formatSalary(vacancy.salary_min) }} - {{ formatSalary(vacancy.salary_max) }} {{ $t('som') }}
                    </template>
                    <template v-else-if="vacancy.salary_min">
                      {{ $t('salary_from') }} {{ formatSalary(vacancy.salary_min) }} {{ $t('som') }}
                    </template>
                    <template v-else>
                      {{ $t('salary_to') }} {{ formatSalary(vacancy.salary_max) }} {{ $t('som') }}
                    </template>
                  </span>
                  <span v-else class="text-surface-400 font-medium">{{ $t('negotiable') }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div v-if="vacancy.description" class="bg-white rounded-xl p-5 border border-surface-100">
            <h2 class="text-lg font-semibold mb-3">{{ $t('description') }}</h2>
            <div class="prose prose-sm max-w-none text-surface-700" v-html="vacancy.description"></div>
          </div>

          <!-- Requirements -->
          <div v-if="vacancy.requirements" class="bg-white rounded-xl p-5 border border-surface-100">
            <h2 class="text-lg font-semibold mb-3">{{ $t('requirements') }}</h2>
            <div class="prose prose-sm max-w-none text-surface-700" v-html="vacancy.requirements"></div>
          </div>

          <!-- Responsibilities -->
          <div v-if="vacancy.responsibilities" class="bg-white rounded-xl p-5 border border-surface-100">
            <h2 class="text-lg font-semibold mb-3">{{ $t('responsibilities') }}</h2>
            <div class="prose prose-sm max-w-none text-surface-700" v-html="vacancy.responsibilities"></div>
          </div>

          <!-- Map -->
          <div v-if="vacancy.latitude && vacancy.longitude" class="bg-white rounded-xl p-5 border border-surface-100">
            <h2 class="text-lg font-semibold mb-3">{{ $t('location_on_map') }}</h2>
            <VacancyMap :lat="vacancy.latitude" :lng="vacancy.longitude" />
          </div>

          <!-- Similar vacancies -->
          <div v-if="similar?.length" class="mt-6">
            <h2 class="text-lg font-semibold mb-4">{{ $t('similar_vacancies') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
              <VacancyCard v-for="v in similar" :key="v.id" :vacancy="v" />
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="lg:w-80 flex-shrink-0 mt-4 lg:mt-0 space-y-4">
          <CompanyCard :employer="vacancy.employer" />
          <ApplyForm :vacancy="vacancy" />
        </aside>
      </div>
    </template>

    <!-- Not found -->
    <div v-else class="text-center py-20">
      <p class="text-surface-500">Vakansiya topilmadi</p>
      <router-link to="/vacancies" class="text-brand-500 hover:underline mt-2 inline-block">
        {{ $t('vacancies') }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useApi } from '@website/composables/useApi';
import { useSeo } from '@website/composables/useSeo';
import BreadcrumbNav from '@website/components/BreadcrumbNav.vue';
import VacancyCard from '@website/components/VacancyCard.vue';
import CompanyCard from '@website/components/CompanyCard.vue';
import ApplyForm from '@website/components/ApplyForm.vue';
import VacancyMap from '@website/components/VacancyMap.vue';
import PageSkeleton from '@website/components/PageSkeleton.vue';

const route = useRoute();
const { t } = useI18n();
const { getVacancy } = useApi();
const seo = useSeo();

const vacancy = ref(null);
const similar = ref([]);
const loading = ref(true);

const breadcrumbs = computed(() => [
  { label: t('home'), to: '/' },
  { label: t('vacancies'), to: '/vacancies' },
  { label: vacancy.value?.title || '...' },
]);

onMounted(async () => {
  try {
    const res = await getVacancy(route.params.id);
    vacancy.value = res.data.vacancy;
    similar.value = res.data.similar || [];

    seo.set({
      title: `${vacancy.value.title} — KadrGo`,
      description: vacancy.value.employer?.company_name + ' — ' + (vacancy.value.city || ''),
      ogTitle: vacancy.value.title,
      ogDescription: vacancy.value.employer?.company_name,
    });
  } catch (e) {
    console.error('Vacancy fetch error:', e);
  } finally {
    loading.value = false;
  }
});

function formatSalary(value) {
  if (!value) return '';
  return new Intl.NumberFormat('uz-UZ').format(value);
}
</script>
