<template>
  <div class="pb-20" style="background-color: var(--tg-theme-bg-color);">
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <LoadingSpinner />
    </div>

    <!-- Error -->
    <div v-else-if="!profile" class="px-5 py-16 text-center">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-30" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
      </svg>
      <p class="text-[14px]" style="color: var(--tg-theme-hint-color);">
        {{ t('candidate.not_found') }}
      </p>
    </div>

    <!-- Profile Content -->
    <template v-else>
      <!-- Header -->
      <div class="px-5 pt-6 pb-5" style="background-color: var(--tg-theme-secondary-bg-color);">
        <div class="flex items-center gap-4">
          <!-- Avatar -->
          <div class="relative">
            <img
              v-if="profile.photo_url"
              :src="profile.photo_url"
              class="w-16 h-16 rounded-full object-cover"
            />
            <div
              v-else
              class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold"
              style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
            >
              {{ getInitial(profile.full_name) }}
            </div>
          </div>

          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold truncate" style="color: var(--tg-theme-text-color);">
              {{ profile.full_name || t('candidate.no_name') }}
            </h2>
            <p v-if="profile.specialty" class="text-[13px] truncate" style="color: var(--tg-theme-button-color);">
              {{ profile.specialty }}
            </p>
            <div class="flex items-center gap-2 mt-1 text-[12px]" style="color: var(--tg-theme-hint-color);">
              <span v-if="profile.city" class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                {{ profile.district ? profile.district + ', ' + profile.city : profile.city }}
              </span>
              <span v-if="profile.experience_years">
                {{ profile.experience_years }} {{ t('common.year') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Match score badge (if passed via query) -->
        <div v-if="matchScore" class="mt-3 flex items-center gap-2 px-3 py-2 rounded-xl"
             :style="{ backgroundColor: matchScore >= 70 ? 'rgba(16,185,129,0.1)' : matchScore >= 40 ? 'rgba(245,158,11,0.1)' : 'rgba(156,163,175,0.1)' }">
          <span class="text-[13px] font-bold"
                :style="{ color: matchScore >= 70 ? '#10b981' : matchScore >= 40 ? '#f59e0b' : '#9ca3af' }">
            {{ matchScore }}%
          </span>
          <span class="text-[12px]" style="color: var(--tg-theme-hint-color);">
            {{ t('candidate.match_score') }}
          </span>
        </div>
      </div>

      <div class="px-5 pt-4 space-y-3">
        <!-- Contact Info -->
        <div v-if="profile.phone || profile.telegram_username" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.contact') }}
          </h3>
          <div class="space-y-2">
            <!-- Phone -->
            <a v-if="profile.phone"
               :href="'tel:' + profile.phone"
               class="flex items-center gap-3 p-2.5 rounded-xl active:opacity-70 transition-opacity"
               style="background-color: var(--tg-theme-bg-color);">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                   style="background-color: rgba(16,185,129,0.1);">
                <svg class="w-4 h-4" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                </svg>
              </div>
              <div class="flex-1">
                <div class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">{{ profile.phone }}</div>
                <div class="text-[11px]" style="color: var(--tg-theme-hint-color);">{{ t('candidate.call') }}</div>
              </div>
              <svg class="w-4 h-4 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </a>
            <!-- Telegram -->
            <a v-if="profile.telegram_username"
               :href="'https://t.me/' + profile.telegram_username"
               target="_blank"
               class="flex items-center gap-3 p-2.5 rounded-xl active:opacity-70 transition-opacity"
               style="background-color: var(--tg-theme-bg-color);">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                   style="background-color: rgba(13,148,136,0.1);">
                <svg class="w-4 h-4" style="color: #0D9488;" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
              </div>
              <div class="flex-1">
                <div class="text-[13px] font-medium" style="color: var(--tg-theme-text-color);">@{{ profile.telegram_username }}</div>
                <div class="text-[11px]" style="color: var(--tg-theme-hint-color);">Telegram</div>
              </div>
              <svg class="w-4 h-4 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Basic Info -->
        <div class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.basic_info') }}
          </h3>
          <div class="space-y-3">
            <InfoRow :label="t('candidate.specialty')" :value="profile.specialty" />
            <InfoRow :label="t('candidate.city')" :value="profile.district ? profile.district + ', ' + profile.city : profile.city" />
            <InfoRow :label="t('candidate.experience')" :value="profile.experience_years != null ? profile.experience_years + ' ' + t('common.year') : null" />
            <InfoRow :label="t('candidate.education')" :value="getEducationLabel(profile.education_level)" />
            <InfoRow :label="t('candidate.salary')" :value="formatSalaryRange(profile)" />
            <InfoRow v-if="profile.gender" :label="t('candidate.gender')" :value="profile.gender === 'male' ? t('candidate.male') : t('candidate.female')" />
            <InfoRow v-if="profile.birth_date" :label="t('candidate.age')" :value="getAge(profile.birth_date)" />
          </div>
        </div>

        <!-- Work Types -->
        <div v-if="profile.work_types?.length" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.work_types') }}
          </h3>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="wt in profile.work_types"
              :key="wt"
              class="px-3 py-1 rounded-lg text-[12px] font-medium"
              style="background-color: rgba(13,148,136,0.1); color: var(--tg-theme-button-color);"
            >
              {{ getWorkTypeLabel(wt) }}
            </span>
          </div>
        </div>

        <!-- Skills -->
        <div v-if="profile.skills?.length" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.skills') }}
          </h3>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="skill in profile.skills"
              :key="skill"
              class="px-3 py-1 rounded-lg text-[12px] font-medium"
              style="background-color: var(--tg-theme-bg-color); color: var(--tg-theme-text-color);"
            >
              {{ skill }}
            </span>
          </div>
        </div>

        <!-- Bio -->
        <div v-if="profile.bio" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-2" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.about') }}
          </h3>
          <p class="text-[13px] whitespace-pre-line" style="color: var(--tg-theme-hint-color);">
            {{ profile.bio }}
          </p>
        </div>

        <!-- Work Experience -->
        <div v-if="profile.work_experience?.length" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.work_experience') }}
          </h3>
          <div class="space-y-3">
            <div v-for="(exp, idx) in profile.work_experience" :key="idx">
              <div v-if="idx > 0" class="h-px mb-3" style="background-color: var(--separator-color);"></div>
              <div class="flex items-start gap-3">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                  style="background-color: rgba(249,115,22,0.1);"
                >
                  <span class="text-[11px] font-bold" style="color: #f97316;">{{ idx + 1 }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-[14px] font-semibold" style="color: var(--tg-theme-text-color);">{{ exp.position }}</div>
                  <div class="text-[13px] mt-0.5" style="color: var(--tg-theme-hint-color);">{{ exp.company }}</div>
                  <div class="text-[12px] mt-1" style="color: var(--tg-theme-hint-color); opacity: 0.7;">
                    {{ formatExpDate(exp.start_date) }} — {{ exp.end_date ? formatExpDate(exp.end_date) : t('candidate.present') }}
                  </div>
                  <div v-if="exp.description" class="text-[12px] mt-1.5" style="color: var(--tg-theme-hint-color);">
                    {{ exp.description }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Links (Resume, LinkedIn) -->
        <div v-if="profile.resume_file_url || profile.linkedin_url" class="rounded-2xl p-4" style="background-color: var(--tg-theme-secondary-bg-color);">
          <h3 class="text-[14px] font-bold mb-3" style="color: var(--tg-theme-text-color);">
            {{ t('candidate.links') }}
          </h3>
          <div class="space-y-2">
            <a v-if="profile.resume_file_url"
               :href="profile.resume_file_url"
               target="_blank"
               class="flex items-center gap-3 p-2.5 rounded-xl active:opacity-70 transition-opacity"
               style="background-color: var(--tg-theme-bg-color);">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                   style="background-color: rgba(239,68,68,0.1);">
                <svg class="w-4 h-4" style="color: #ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
              </div>
              <span class="text-[13px] font-medium flex-1" style="color: var(--tg-theme-text-color);">{{ t('candidate.resume') }}</span>
              <svg class="w-4 h-4 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
            <a v-if="profile.linkedin_url"
               :href="profile.linkedin_url"
               target="_blank"
               class="flex items-center gap-3 p-2.5 rounded-xl active:opacity-70 transition-opacity"
               style="background-color: var(--tg-theme-bg-color);">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                   style="background-color: rgba(13,148,136,0.1);">
                <svg class="w-4 h-4" style="color: #0077b5;" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
              </div>
              <span class="text-[13px] font-medium flex-1" style="color: var(--tg-theme-text-color);">LinkedIn</span>
              <svg class="w-4 h-4 flex-shrink-0" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue'
import { useRoute } from 'vue-router'
import { useLocale } from '@/composables/useLocale'
import { formatNumber, getInitial as _getInitial } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'

const route = useRoute()
const { t } = useLocale()

const profile = ref(null)
const loading = ref(true)

const matchScore = computed(() => {
  const score = Number(route.query.score)
  return score > 0 ? score : null
})

onMounted(async () => {
  try {
    const res = await api.get(`/workers/${route.params.id}`)
    profile.value = res.data.profile
  } catch {
    // silent
  } finally {
    loading.value = false
  }
})

function getInitial(name) {
  return _getInitial(name)
}

function formatSalaryRange(p) {
  if (p?.expected_salary_min && p?.expected_salary_max) {
    return `${formatNumber(p.expected_salary_min)} - ${formatNumber(p.expected_salary_max)} ${t('common.som')}`
  } else if (p?.expected_salary_min) {
    return `${formatNumber(p.expected_salary_min)} ${t('common.som_from')}`
  }
  return t('common.not_specified')
}

function getEducationLabel(level) {
  if (!level) return null
  const labels = {
    secondary: t('candidate.edu_secondary'),
    vocational: t('candidate.edu_vocational'),
    bachelor: t('candidate.edu_bachelor'),
    master: t('candidate.edu_master'),
    phd: t('candidate.edu_phd'),
  }
  return labels[level] || level
}

function getAge(birthDate) {
  if (!birthDate) return null
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const m = today.getMonth() - birth.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--
  return age + ' ' + t('candidate.age_suffix')
}

function getWorkTypeLabel(type) {
  const labels = {
    full_time: t('work_type.full_time'),
    part_time: t('work_type.part_time'),
    remote: t('work_type.remote'),
    temporary: t('work_type.temporary'),
  }
  return labels[type] || type
}

function formatExpDate(dateStr) {
  if (!dateStr) return ''
  const [y, m] = dateStr.split('-').map(Number)
  const months = t('edit_profile.months_short')
  return `${months[m - 1]} ${y}`
}

// Simple InfoRow component inline
const InfoRow = (props) => {
  if (!props.value) return null
  return h('div', {}, [
    h('div', { class: 'flex justify-between items-center' }, [
      h('span', {
        class: 'text-[13px]',
        style: 'color: var(--tg-theme-hint-color);',
      }, props.label),
      h('span', {
        class: 'text-[13px] font-medium text-right max-w-[60%]',
        style: 'color: var(--tg-theme-text-color);',
      }, props.value),
    ]),
    h('div', {
      class: 'h-px mt-3',
      style: 'background-color: var(--separator-color);',
    }),
  ])
}
InfoRow.props = ['label', 'value']
</script>
