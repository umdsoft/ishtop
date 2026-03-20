<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ user?.first_name }} {{ user?.last_name }}</h1>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-xl border border-surface-200 dark:border-surface-700/60 bg-white dark:bg-surface-800/80 p-6 animate-pulse">
          <div class="h-5 w-40 bg-surface-200 dark:bg-surface-700 rounded mb-6" />
          <div class="grid grid-cols-2 gap-4">
            <div v-for="i in 8" :key="i">
              <div class="h-3 w-20 bg-surface-200 dark:bg-surface-700 rounded mb-2" />
              <div class="h-5 w-32 bg-surface-100 dark:bg-surface-700/50 rounded" />
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-surface-200 dark:border-surface-700/60 bg-white dark:bg-surface-800/80 p-6 animate-pulse">
          <div class="h-5 w-24 bg-surface-200 dark:bg-surface-700 rounded mb-4" />
          <div class="h-10 w-full bg-surface-200 dark:bg-surface-700 rounded-lg mb-4" />
          <div class="h-px bg-surface-200 dark:bg-surface-700 my-4" />
          <div class="space-y-2">
            <div class="h-4 w-full bg-surface-100 dark:bg-surface-700/50 rounded" />
            <div class="h-4 w-3/4 bg-surface-100 dark:bg-surface-700/50 rounded" />
          </div>
        </div>
      </div>
    </div>

    <template v-else-if="user">
      <!-- User Profile Card + Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <AppCard class="lg:col-span-2">
          <div class="flex items-start gap-4 mb-6">
            <!-- Avatar -->
            <div class="shrink-0">
              <div v-if="user.avatar_url" class="w-16 h-16 rounded-full overflow-hidden ring-2 ring-surface-200 dark:ring-surface-700">
                <img :src="user.avatar_url" :alt="user.first_name" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-16 h-16 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center ring-2 ring-surface-200 dark:ring-surface-700">
                <span class="text-xl font-bold text-brand-600 dark:text-brand-400">{{ (user.first_name?.[0] || '').toUpperCase() }}</span>
              </div>
            </div>
            <!-- Name + badges -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ user.first_name }} {{ user.last_name || '' }}</h2>
                <span v-if="user.is_verified" class="text-xs px-2 py-0.5 rounded-full font-medium bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                  Tasdiqlangan
                </span>
                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', user.is_blocked ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400']">
                  {{ user.is_blocked ? 'Bloklangan' : 'Faol' }}
                </span>
              </div>
              <p v-if="user.username" class="text-sm text-surface-500 dark:text-surface-400 mt-0.5">@{{ user.username }}</p>
              <div class="flex items-center gap-3 mt-2 flex-wrap">
                <span v-if="user.worker_profile" class="text-xs px-2 py-0.5 rounded-full bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400 font-medium">
                  Ishchi
                </span>
                <span v-if="user.employer_profiles?.length" class="text-xs px-2 py-0.5 rounded-full bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400 font-medium">
                  Ish beruvchi
                </span>
                <span v-for="role in user.roles" :key="role.id" class="text-xs px-2 py-0.5 bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 rounded-full font-medium">
                  {{ role.name }}
                </span>
              </div>
            </div>
          </div>

          <!-- User Info Grid -->
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <InfoItem label="Telefon" :value="user.phone" icon="phone" />
            <InfoItem label="Email" :value="user.email" />
            <InfoItem label="Telegram ID" :value="user.telegram_id" />
            <InfoItem label="Til" :value="langLabel(user.language)" />
            <InfoItem label="Ro'yxatdan o'tgan" :value="formatDateTime(user.created_at)" />
            <InfoItem label="Oxirgi faollik" :value="formatDateTime(user.last_active_at)" />
            <InfoItem label="Balans" :value="formatCurrency(user.balance)" />
            <InfoItem label="Referral kodi" :value="user.referral_code" />
          </dl>
        </AppCard>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
          <AppCard title="Amallar">
            <div class="space-y-3">
              <button
                @click="toggleBlock"
                :class="['w-full py-2.5 px-4 text-sm font-medium rounded-lg transition-colors', user.is_blocked ? 'bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400' : 'bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400']"
              >
                {{ user.is_blocked ? 'Blokdan chiqarish' : 'Bloklash' }}
              </button>
              <button
                v-if="user.telegram_id"
                @click="openTelegram"
                class="w-full py-2.5 px-4 text-sm font-medium rounded-lg bg-[#229ED9]/10 text-[#229ED9] hover:bg-[#229ED9]/20 transition-colors"
              >
                Telegram'da ochish
              </button>
            </div>
          </AppCard>

          <!-- Stats -->
          <AppCard title="Statistika">
            <div class="space-y-3">
              <StatRow label="Referrallar" :value="user.referrals_count || 0" />
              <StatRow label="To'lovlar" :value="user.payments_count || 0" />
              <StatRow v-if="user.worker_profile" label="Arizalar" :value="user.worker_profile.applications_count || 0" />
              <StatRow v-if="user.employer_profiles?.length" label="Kompaniyalar" :value="user.employer_profiles.length" />
              <StatRow
                v-if="user.employer_profiles?.length"
                label="Vakansiyalar"
                :value="user.employer_profiles.reduce((sum, e) => sum + (e.vacancies_count || 0), 0)"
              />
            </div>
          </AppCard>
        </div>
      </div>

      <!-- Worker Profile -->
      <AppCard v-if="user.worker_profile" title="Ishchi profili">
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
          <InfoItem label="To'liq ism" :value="user.worker_profile.full_name" />
          <InfoItem label="Tug'ilgan sana" :value="formatDate(user.worker_profile.birth_date)" />
          <InfoItem label="Jinsi" :value="genderLabel(user.worker_profile.gender)" />
          <InfoItem label="Viloyat" :value="user.worker_profile.city" />
          <InfoItem label="Shahar/Tuman" :value="user.worker_profile.district" />
          <InfoItem label="Ta'lim" :value="educationLabel(user.worker_profile.education_level)" />
          <InfoItem label="Mutaxassislik" :value="user.worker_profile.specialty" />
          <InfoItem label="Tajriba" :value="user.worker_profile.experience_years != null ? `${user.worker_profile.experience_years} yil` : null" />
          <InfoItem label="Qidiruv holati" :value="searchStatusLabel(user.worker_profile.search_status)" />
          <InfoItem label="Kutilgan maosh" :value="salaryRange(user.worker_profile.expected_salary_min, user.worker_profile.expected_salary_max)" />
          <InfoItem label="Ko'rishlar" :value="user.worker_profile.views_count" />
        </dl>

        <!-- Skills -->
        <div v-if="user.worker_profile.skills?.length" class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800">
          <h4 class="text-sm font-medium text-surface-500 dark:text-surface-400 mb-2">Ko'nikmalar</h4>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="skill in user.worker_profile.skills"
              :key="skill"
              class="text-xs px-2.5 py-1 rounded-full bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300 font-medium"
            >
              {{ skill }}
            </span>
          </div>
        </div>

        <!-- Bio -->
        <div v-if="user.worker_profile.bio" class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800">
          <h4 class="text-sm font-medium text-surface-500 dark:text-surface-400 mb-2">Haqida</h4>
          <p class="text-sm text-surface-700 dark:text-surface-300 whitespace-pre-line">{{ user.worker_profile.bio }}</p>
        </div>

        <!-- Work Experience -->
        <div v-if="user.worker_profile.work_experience?.length" class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800">
          <h4 class="text-sm font-medium text-surface-500 dark:text-surface-400 mb-2">Ish tajribasi</h4>
          <div class="space-y-3">
            <div
              v-for="(exp, i) in user.worker_profile.work_experience"
              :key="i"
              class="p-3 rounded-lg bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700/60"
            >
              <p class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ exp.position || exp.title }}</p>
              <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">{{ exp.company }} <span v-if="exp.period">· {{ exp.period }}</span></p>
              <p v-if="exp.description" class="text-xs text-surface-600 dark:text-surface-400 mt-1">{{ exp.description }}</p>
            </div>
          </div>
        </div>

        <!-- Resume link -->
        <div v-if="user.worker_profile.resume_file_url" class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800">
          <a
            :href="user.worker_profile.resume_file_url"
            target="_blank"
            class="inline-flex items-center gap-2 text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline"
          >
            <DocumentTextIcon class="w-4 h-4" />
            Rezyumeni ko'rish
          </a>
        </div>
      </AppCard>

      <!-- Employer Profiles -->
      <AppCard v-if="user.employer_profiles?.length" title="Kompaniyalar">
        <div class="space-y-4">
          <div
            v-for="employer in user.employer_profiles"
            :key="employer.id"
            class="p-4 rounded-xl border border-surface-200 dark:border-surface-700/60 bg-surface-50/50 dark:bg-surface-800/30 hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors cursor-pointer"
            @click="$router.push(`/employers/${employer.id}`)"
          >
            <div class="flex items-start gap-3">
              <div v-if="employer.logo_url" class="w-10 h-10 rounded-lg overflow-hidden shrink-0 bg-white dark:bg-surface-800 ring-1 ring-surface-200 dark:ring-surface-700">
                <img :src="employer.logo_url" :alt="employer.company_name" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-10 h-10 rounded-lg shrink-0 bg-surface-200 dark:bg-surface-700 flex items-center justify-center">
                <BuildingOfficeIcon class="w-5 h-5 text-surface-400" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h4 class="text-sm font-semibold text-surface-900 dark:text-surface-100">{{ employer.company_name }}</h4>
                  <span v-if="employer.verification_level === 'verified' || employer.verification_level === 'top'"
                    class="text-xs px-1.5 py-0.5 rounded bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400 font-medium"
                  >
                    Tasdiqlangan
                  </span>
                  <span v-if="user.active_employer_id === employer.id"
                    class="text-xs px-1.5 py-0.5 rounded bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 font-medium"
                  >
                    Aktiv
                  </span>
                </div>
                <div class="flex items-center gap-3 mt-1 text-xs text-surface-500 dark:text-surface-400">
                  <span v-if="employer.industry">{{ employer.industry }}</span>
                  <span v-if="employer.phone">{{ employer.phone }}</span>
                  <span>{{ employer.vacancies_count || 0 }} vakansiya</span>
                  <span v-if="employer.rating">{{ employer.rating }} ★ ({{ employer.rating_count }})</span>
                </div>
                <p v-if="employer.description" class="text-xs text-surface-600 dark:text-surface-400 mt-1.5 line-clamp-2">{{ employer.description }}</p>
              </div>
              <ChevronRightIcon class="w-4 h-4 text-surface-400 shrink-0" />
            </div>
          </div>
        </div>
      </AppCard>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import {
  ArrowLeftIcon,
  DocumentTextIcon,
  BuildingOfficeIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';
import { formatDateTime, formatDate } from '@/shared/formatters';

const route = useRoute();
const user = ref(null);
const loading = ref(true);

// Info item sub-component
const InfoItem = (props) => {
  const val = props.value != null && props.value !== '' ? String(props.value) : '—';
  return h('div', [
    h('dt', { class: 'text-xs font-medium text-surface-500 dark:text-surface-400 mb-0.5' }, props.label),
    h('dd', { class: 'text-sm font-medium text-surface-900 dark:text-surface-100' }, val),
  ]);
};
InfoItem.props = ['label', 'value', 'icon'];

// Stat row sub-component
const StatRow = (props) => {
  return h('div', { class: 'flex items-center justify-between' }, [
    h('span', { class: 'text-sm text-surface-600 dark:text-surface-400' }, props.label),
    h('span', { class: 'text-sm font-semibold text-surface-900 dark:text-surface-100' }, String(props.value)),
  ]);
};
StatRow.props = ['label', 'value'];

function formatCurrency(n) {
  if (n == null) return null;
  return Number(n).toLocaleString() + " so'm";
}

function salaryRange(min, max) {
  if (!min && !max) return null;
  if (min && max) return `${Number(min).toLocaleString()} — ${Number(max).toLocaleString()} so'm`;
  if (min) return `${Number(min).toLocaleString()}+ so'm`;
  return `${Number(max).toLocaleString()} gacha so'm`;
}

function langLabel(lang) {
  return { uz: "O'zbekcha", ru: 'Ruscha', en: 'Inglizcha' }[lang] || lang;
}

function genderLabel(g) {
  return { male: 'Erkak', female: 'Ayol' }[g] || g;
}

function educationLabel(e) {
  const map = {
    secondary: "O'rta",
    vocational: "O'rta maxsus",
    higher: 'Oliy',
    bachelor: 'Bakalavr',
    master: 'Magistr',
    phd: 'PhD',
    incomplete_higher: "Oliy (tugallanmagan)",
  };
  return map[e] || e;
}

function searchStatusLabel(s) {
  return { open: 'Ish qidirmoqda', paused: 'Pauza', closed: 'Qidirmayapti' }[s] || s;
}

function openTelegram() {
  window.open(`https://t.me/${user.value.username || user.value.telegram_id}`, '_blank');
}

async function fetchUser() {
  try {
    const res = await axios.get(`/api/admin/users/${route.params.id}`);
    user.value = res.data.user;
  } catch (err) {
    toast.error('Foydalanuvchi topilmadi');
  } finally {
    loading.value = false;
  }
}

async function toggleBlock() {
  try {
    const res = await axios.post(`/api/admin/users/${user.value.id}/toggle-block`);
    user.value.is_blocked = res.data.user.is_blocked;
    toast.success(res.data.message);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchUser);
</script>
