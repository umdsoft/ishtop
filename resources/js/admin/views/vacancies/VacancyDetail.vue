<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ vacancy?.title_uz || vacancy?.title_ru || 'Vakansiya' }}</h1>
          <p v-if="vacancy?.title_ru && vacancy?.title_uz" class="text-sm text-surface-500 mt-0.5">{{ vacancy.title_ru }}</p>
        </div>
        <span v-if="vacancy" :class="['text-xs px-2.5 py-1 rounded-full font-semibold', statusClass(vacancy.status)]">
          {{ statusLabel(vacancy.status) }}
        </span>
        <span v-if="vacancy?.is_top" class="text-xs px-2 py-0.5 rounded-full font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">TOP</span>
      </div>
      <div v-if="vacancy?.status === 'pending'" class="flex items-center gap-2">
        <button @click="approveVacancy" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-success-500 text-white hover:bg-success-600 transition-colors">
          <CheckIcon class="w-4 h-4" /> Tasdiqlash
        </button>
        <button @click="rejectVacancy" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-danger-500 text-white hover:bg-danger-600 transition-colors">
          <XMarkIcon class="w-4 h-4" /> Rad etish
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="vacancy">
      <!-- Stats row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ vacancy.views_count || 0 }}</p>
          <p class="text-xs text-surface-500 mt-1">Ko'rishlar</p>
        </div>
        <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ vacancy.applications_count || 0 }}</p>
          <p class="text-xs text-surface-500 mt-1">Arizalar</p>
        </div>
        <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ formatDateShort(vacancy.published_at) || '—' }}</p>
          <p class="text-xs text-surface-500 mt-1">E'lon qilingan</p>
        </div>
        <div class="bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl p-4 text-center">
          <p class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ formatDateShort(vacancy.expires_at) || '—' }}</p>
          <p class="text-xs text-surface-500 mt-1">Muddati</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column: Vakansiya ma'lumotlari -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Asosiy ma'lumotlar -->
          <AppCard title="Asosiy ma'lumotlar">
            <div class="grid grid-cols-2 gap-x-6 gap-y-4">
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Kategoriya</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">
                  <span v-if="vacancy.category_relation">
                    {{ vacancy.category_relation.emoji }} {{ vacancy.category_relation.name_uz }}
                    <span v-if="vacancy.category_relation.name_ru" class="text-surface-400 text-xs">/ {{ vacancy.category_relation.name_ru }}</span>
                  </span>
                  <span v-else-if="vacancy.category_name">{{ vacancy.category_name }}</span>
                  <span v-else-if="vacancy.category">{{ vacancy.category }}</span>
                  <span v-else class="text-surface-400">—</span>
                </dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Ish turi</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ vacancy.work_type_label || workTypeLabel(vacancy.work_type) }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Maosh</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">
                  <span v-if="vacancy.salary_min || vacancy.salary_max">
                    <span v-if="vacancy.salary_min">{{ Number(vacancy.salary_min).toLocaleString() }}</span>
                    <span v-if="vacancy.salary_min && vacancy.salary_max"> — </span>
                    <span v-if="vacancy.salary_max">{{ Number(vacancy.salary_max).toLocaleString() }}</span>
                    <span class="text-surface-400 text-xs ml-1">so'm</span>
                  </span>
                  <span v-else class="text-surface-400">Kelishiladi</span>
                </dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Tajriba</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ experienceLabel(vacancy.experience_required) }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Shahar</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ vacancy.city || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Tuman/Rayon</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ vacancy.district || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Aloqa telefoni</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ vacancy.contact_phone || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Aloqa usuli</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ vacancy.contact_method || '—' }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Til</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ vacancy.language === 'ru' ? 'Ruscha' : "O'zbekcha" }}</dd>
              </div>
              <div>
                <dt class="text-xs text-surface-500 mb-0.5">Yaratilgan</dt>
                <dd class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ formatDate(vacancy.created_at) }}</dd>
              </div>
            </div>
          </AppCard>

          <!-- Tavsif: uz/ru tab -->
          <AppCard>
            <template #header>
              <div class="flex items-center gap-2 px-6 py-3">
                <button
                  v-for="lang in ['uz', 'ru']" :key="lang"
                  @click="descLang = lang"
                  :class="['px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                    descLang === lang
                      ? 'bg-brand-500 text-white'
                      : 'text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-800']"
                >
                  {{ lang === 'uz' ? "O'zbekcha" : 'Ruscha' }}
                </button>
              </div>
            </template>
            <div v-if="descLang === 'uz'">
              <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">Tavsif</h4>
              <p class="text-surface-900 dark:text-surface-100 whitespace-pre-line text-sm leading-relaxed">{{ vacancy.description_uz || '—' }}</p>
              <template v-if="vacancy.requirements_uz">
                <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2 mt-5">Talablar</h4>
                <p class="text-surface-900 dark:text-surface-100 whitespace-pre-line text-sm leading-relaxed">{{ vacancy.requirements_uz }}</p>
              </template>
              <template v-if="vacancy.responsibilities_uz">
                <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2 mt-5">Mas'uliyatlar</h4>
                <p class="text-surface-900 dark:text-surface-100 whitespace-pre-line text-sm leading-relaxed">{{ vacancy.responsibilities_uz }}</p>
              </template>
            </div>
            <div v-else>
              <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">Описание</h4>
              <p class="text-surface-900 dark:text-surface-100 whitespace-pre-line text-sm leading-relaxed">{{ vacancy.description_ru || '—' }}</p>
              <template v-if="vacancy.requirements_ru">
                <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2 mt-5">Требования</h4>
                <p class="text-surface-900 dark:text-surface-100 whitespace-pre-line text-sm leading-relaxed">{{ vacancy.requirements_ru }}</p>
              </template>
              <template v-if="vacancy.responsibilities_ru">
                <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2 mt-5">Обязанности</h4>
                <p class="text-surface-900 dark:text-surface-100 whitespace-pre-line text-sm leading-relaxed">{{ vacancy.responsibilities_ru }}</p>
              </template>
            </div>
          </AppCard>
        </div>

        <!-- Right sidebar: Ish beruvchi + Nomzodlar -->
        <div class="space-y-6">
          <!-- Ish beruvchi -->
          <AppCard title="Ish beruvchi">
            <div v-if="vacancy.employer" class="space-y-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 font-bold">
                  {{ (vacancy.employer.company_name?.[0] || 'K').toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-surface-900 dark:text-surface-100 text-sm">{{ vacancy.employer.company_name }}</p>
                  <p v-if="vacancy.employer.user" class="text-xs text-surface-500">{{ vacancy.employer.user.first_name }} {{ vacancy.employer.user.last_name }}</p>
                </div>
              </div>
              <div class="space-y-2 text-sm">
                <div v-if="vacancy.employer.phone" class="flex items-center gap-2">
                  <PhoneIcon class="w-3.5 h-3.5 text-surface-400 shrink-0" />
                  <a :href="`tel:${vacancy.employer.phone}`" class="text-surface-700 dark:text-surface-300 hover:text-brand-600">{{ vacancy.employer.phone }}</a>
                </div>
                <div v-if="vacancy.employer.user?.username" class="flex items-center gap-2">
                  <svg class="w-3.5 h-3.5 text-[#0088cc] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                  <a :href="`https://t.me/${vacancy.employer.user.username}`" target="_blank" class="text-[#0088cc] hover:underline">@{{ vacancy.employer.user.username }}</a>
                </div>
                <div v-if="vacancy.employer.user?.telegram_id" class="text-surface-400 text-xs">
                  ID: {{ vacancy.employer.user.telegram_id }}
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-surface-500">Ma'lumot yo'q</p>
          </AppCard>

          <!-- Nomzodlar (Sidebar) -->
          <AppCard noPadding>
            <div class="px-6 py-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                Nomzodlar
                <span v-if="vacancy.applications?.length" class="text-sm font-normal text-surface-500">({{ vacancy.applications.length }})</span>
              </h3>
            </div>

            <!-- Stage filter pills -->
            <div v-if="vacancy.stage_counts && Object.keys(vacancy.stage_counts).length" class="px-6 pb-3 flex flex-wrap gap-1.5">
              <button
                @click="stageFilter = null"
                :class="['px-2 py-0.5 text-[11px] font-medium rounded-full transition-colors',
                  !stageFilter ? 'bg-brand-500 text-white' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400']"
              >Barchasi</button>
              <button
                v-for="(count, stage) in vacancy.stage_counts" :key="stage"
                @click="stageFilter = stage"
                :class="['px-2 py-0.5 text-[11px] font-medium rounded-full transition-colors',
                  stageFilter === stage ? 'bg-brand-500 text-white' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400']"
              >{{ stageLabel(stage) }} {{ count }}</button>
            </div>

            <div v-if="!vacancy.applications?.length" class="px-6 pb-6 text-center">
              <UserGroupIcon class="w-8 h-8 mx-auto text-surface-300 dark:text-surface-600 mb-2" />
              <p class="text-sm text-surface-500">Hali nomzodlar yo'q</p>
            </div>

            <div v-else class="max-h-[600px] overflow-y-auto">
              <div
                v-for="app in filteredApplications" :key="app.id"
                class="px-4 py-3 hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors"
              >
                <div class="flex items-start gap-3">
                  <!-- Avatar -->
                  <div class="shrink-0">
                    <img
                      v-if="app.worker?.photo_url"
                      :src="app.worker.photo_url"
                      class="w-9 h-9 rounded-full object-cover"
                    >
                    <div v-else class="w-9 h-9 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 font-bold text-xs">
                      {{ (app.worker?.full_name?.[0] || app.worker?.user?.first_name?.[0] || '?').toUpperCase() }}
                    </div>
                  </div>

                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                      <p class="text-sm font-medium text-surface-900 dark:text-surface-100 truncate">
                        {{ app.worker?.full_name || [app.worker?.user?.first_name, app.worker?.user?.last_name].filter(Boolean).join(' ') || "Noma'lum" }}
                      </p>
                      <span :class="['text-[9px] px-1.5 py-0.5 rounded-full font-semibold shrink-0', stageClass(app.stage)]">
                        {{ stageLabel(app.stage) }}
                      </span>
                    </div>
                    <p v-if="app.worker?.specialty" class="text-xs text-surface-500 truncate">{{ app.worker.specialty }}</p>
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-surface-400">
                      <span v-if="app.worker?.city">{{ app.worker.city }}</span>
                      <span v-if="app.worker?.experience_years">{{ app.worker.experience_years }} yil</span>
                      <span>{{ formatDateShort(app.created_at) }}</span>
                    </div>
                    <!-- Skills compact -->
                    <div v-if="app.worker?.skills?.length" class="mt-1.5 flex flex-wrap gap-1">
                      <span
                        v-for="skill in app.worker.skills.slice(0, 4)" :key="skill"
                        class="text-[9px] px-1.5 py-0.5 bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 rounded"
                      >{{ skill }}</span>
                      <span v-if="app.worker.skills.length > 4" class="text-[9px] text-surface-400">+{{ app.worker.skills.length - 4 }}</span>
                    </div>
                  </div>

                  <!-- Contact -->
                  <div class="shrink-0 flex flex-col gap-1">
                    <a
                      v-if="app.worker?.user?.username"
                      :href="`https://t.me/${app.worker.user.username}`"
                      target="_blank"
                      class="p-1.5 rounded-lg text-[#0088cc] hover:bg-[#0088cc]/10 transition-colors"
                      title="Telegram"
                    >
                      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    </a>
                    <a
                      v-if="app.worker?.user?.phone"
                      :href="`tel:${app.worker.user.phone}`"
                      class="p-1.5 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
                      title="Telefon"
                    >
                      <PhoneIcon class="w-3.5 h-3.5" />
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import { ArrowLeftIcon, CheckIcon, XMarkIcon, PhoneIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const vacancy = ref(null);
const loading = ref(true);
const descLang = ref('uz');
const stageFilter = ref(null);

const filteredApplications = computed(() => {
  if (!vacancy.value?.applications) return [];
  if (!stageFilter.value) return vacancy.value.applications;
  return vacancy.value.applications.filter(a => a.stage === stageFilter.value);
});

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatDateShort(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function statusClass(status) {
  const map = {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    closed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    expired: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = { active: 'Faol', pending: 'Kutilmoqda', closed: 'Yopilgan', expired: "Muddati o'tgan", rejected: 'Rad etilgan' };
  return map[status] || status;
}

function stageLabel(stage) {
  const map = { new: 'Yangi', reviewed: "Ko'rilgan", shortlisted: 'Tanlangan', interview: 'Intervyu', offered: 'Taklif', hired: 'Qabul', rejected: 'Rad', withdrawn: 'Bekor' };
  return map[stage] || stage;
}

function stageClass(stage) {
  const map = {
    new: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    reviewed: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    shortlisted: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    interview: 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
    offered: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    hired: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    withdrawn: 'bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-400',
  };
  return map[stage] || 'bg-surface-100 text-surface-600';
}

function workTypeLabel(type) {
  const map = { full_time: "To'liq ish kuni", part_time: 'Yarim stavka', remote: 'Masofaviy', temporary: 'Vaqtinchalik' };
  return map[type] || type || '—';
}

function experienceLabel(exp) {
  const map = { no_experience: 'Kerak emas', intern: 'Stajiyor', junior: 'Junior (1-2 yil)', mid: "O'rta (2-4 yil)", senior: 'Senior (4+ yil)', '0-1': '0-1 yil', '1-3': '1-3 yil', '3-5': '3-5 yil', '5+': '5+ yil' };
  return map[exp] || exp || '—';
}

async function fetchVacancy() {
  try {
    const res = await axios.get(`/api/admin/vacancies/${route.params.id}`);
    vacancy.value = res.data.vacancy || res.data;
  } catch (err) {
    toast.error('Vakansiya topilmadi');
  } finally {
    loading.value = false;
  }
}

async function approveVacancy() {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.value.id}/approve`);
    vacancy.value.status = 'active';
    toast.success(res.data.message || 'Vakansiya tasdiqlandi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function rejectVacancy() {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.value.id}/reject`);
    vacancy.value.status = 'rejected';
    toast.success(res.data.message || 'Vakansiya rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchVacancy);
</script>
