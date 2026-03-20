<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Vakansiya yuklanmoqda..." />
    </div>

    <!-- Content -->
    <div v-else-if="vacancy">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button
            class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
            @click="$router.push('/dashboard/vacancies')"
          >
            <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
          </button>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
              <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100 truncate">
                {{ vacancy.title_uz || vacancy.title_ru }}
              </h1>
              <AppBadge :variant="getStatusVariant(vacancy.status)">
                {{ getStatusLabel(vacancy.status) }}
              </AppBadge>
              <span
                v-if="vacancy.language"
                class="px-1.5 py-0.5 text-[10px] font-bold uppercase rounded bg-surface-100 dark:bg-surface-700 text-surface-500 dark:text-surface-400"
              >
                {{ vacancy.language }}
              </span>
            </div>
            <p class="text-surface-600 dark:text-surface-400 mt-1">
              {{ vacancy.employer?.company_name }} &bull; {{ formatDate(vacancy.created_at) }}
            </p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <AppButton
              v-if="['active', 'paused', 'draft'].includes(vacancy.status)"
              :variant="vacancy.status === 'active' ? 'outline' : 'primary'"
              @click="toggleStatus"
            >
              <template #icon-left>
                <component :is="vacancy.status === 'active' ? PauseCircleIcon : PlayCircleIcon" class="h-5 w-5" />
              </template>
              {{ vacancy.status === 'active' ? 'To\'xtatish' : 'Faollashtirish' }}
            </AppButton>
            <AppButton
              variant="outline"
              @click="$router.push(`/dashboard/vacancies/${vacancy.id}/edit`)"
            >
              <template #icon-left>
                <PencilIcon class="h-5 w-5" />
              </template>
              Tahrirlash
            </AppButton>
            <AppDropdown
              :items="actionMenuItems"
              label="Amallar"
              variant="outline"
            />
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Arizalar</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ vacancy.applications_count }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Yangi</p>
              <p class="text-2xl font-bold text-brand-600 dark:text-brand-400 mt-1">
                {{ vacancy.new_applications_count }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Ko'rilgan</p>
              <p class="text-2xl font-bold text-surface-900 dark:text-surface-100 mt-1">
                {{ vacancy.views_count || 0 }}
              </p>
            </div>
          </AppCard>
          <AppCard>
            <div class="text-center">
              <p class="text-sm text-surface-600 dark:text-surface-400">Konversiya</p>
              <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">
                {{ conversionRate }}
              </p>
            </div>
          </AppCard>
        </div>
      </div>

      <!-- Tabs -->
      <AppTabs v-model="activeTab" :tabs="tabs" variant="underline">
        <!-- Pipeline Tab -->
        <template #panel-pipeline>
          <!-- Stage Filter Pills -->
          <div class="flex flex-wrap gap-2 mb-4">
            <button
              v-for="stage in pipelineStages"
              :key="stage.key"
              :class="[
                'px-3 py-1.5 text-sm font-medium rounded-full border transition-colors',
                activeStageFilter === stage.key
                  ? 'bg-brand-50 dark:bg-brand-950/30 border-brand-300 dark:border-brand-700 text-brand-700 dark:text-brand-300'
                  : 'border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-400 hover:bg-surface-50 dark:hover:bg-surface-800',
              ]"
              @click="filterByStage(stage.key)"
            >
              {{ stage.label }}
              <span
                v-if="stageCounts[stage.key]"
                :class="[
                  'ml-1.5 px-1.5 py-0.5 text-xs font-bold rounded-full',
                  activeStageFilter === stage.key
                    ? 'bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300'
                    : 'bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300',
                ]"
              >
                {{ stageCounts[stage.key] }}
              </span>
            </button>
          </div>

          <!-- Applications loading -->
          <div v-if="applicationsLoading" class="flex items-center justify-center py-12">
            <AppLoadingSpinner text="Arizalar yuklanmoqda..." />
          </div>

          <!-- Applications List -->
          <div v-else-if="applications.length > 0" class="space-y-3">
            <div
              v-for="app in applications"
              :key="app.id"
              class="flex items-center gap-4 p-4 bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl hover:border-brand-200 dark:hover:border-brand-800 transition-colors cursor-pointer"
              @click="viewApplication(app)"
            >
              <!-- Avatar -->
              <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-600 dark:text-brand-400 flex items-center justify-center font-semibold text-sm shrink-0">
                {{ getApplicantInitials(app) }}
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <p class="font-medium text-surface-900 dark:text-surface-100 truncate">
                  {{ getApplicantName(app) }}
                </p>
                <div class="flex items-center gap-2 mt-0.5">
                  <span v-if="app.worker?.specialty" class="text-sm text-surface-500 dark:text-surface-400 truncate">
                    {{ app.worker.specialty }}
                  </span>
                  <span v-if="app.worker?.city" class="text-xs text-surface-400">
                    &bull; {{ app.worker.city }}
                  </span>
                </div>
              </div>

              <!-- Score -->
              <div v-if="app.questionnaire_score !== null" class="text-center shrink-0">
                <div
                  :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold',
                    getScoreClasses(app),
                  ]"
                >
                  {{ Math.round(app.questionnaire_score) }}
                </div>
                <p class="text-[10px] text-surface-400 mt-0.5">ball</p>
              </div>

              <!-- Stage badge -->
              <AppBadge :variant="getStageVariant(app.stage)" size="sm">
                {{ getStageLabel(app.stage) }}
              </AppBadge>

              <!-- Date -->
              <span class="text-xs text-surface-400 shrink-0">
                {{ formatRelativeDate(app.created_at) }}
              </span>
            </div>

            <!-- Applications Pagination -->
            <div v-if="applicationsTotalPages > 1" class="flex justify-center pt-4">
              <AppPagination
                v-model:current-page="applicationsPage"
                :total="applicationsTotal"
                :per-page="20"
                @update:current-page="fetchApplications"
              />
            </div>
          </div>

          <!-- Empty -->
          <div v-else class="bg-surface-50 dark:bg-surface-900 rounded-lg p-8 text-center">
            <UsersIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
              Arizalar yo'q
            </h3>
            <p class="text-surface-600 dark:text-surface-400">
              {{ activeStageFilter ? 'Bu bosqichda arizalar topilmadi' : 'Ushbu vakansiyaga hali arizalar kelmagan' }}
            </p>
          </div>
        </template>

        <!-- Recommended Candidates Tab -->
        <template #panel-recommended>
          <!-- Subscription Gate -->
          <div v-if="subscriptionBlocked" class="max-w-lg mx-auto text-center py-16">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
              <LockClosedIcon class="h-8 w-8 text-brand-500" />
            </div>
            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-100 mb-2">
              Nomzodlar bazasi
            </h2>
            <p class="text-surface-600 dark:text-surface-400 mb-6">
              Vakansiyalarga mos nomzodlarni topish imkoniyati faqat Recruiter Pro va undan yuqori rejalarda mavjud.
            </p>
            <router-link to="/dashboard/settings/billing">
              <AppButton variant="primary">
                Obunani yangilash
              </AppButton>
            </router-link>
          </div>

          <!-- Loading -->
          <div v-else-if="candidatesLoading" class="flex items-center justify-center py-12">
            <AppLoadingSpinner size="lg" text="Mos nomzodlar qidirilmoqda..." />
          </div>

          <!-- Empty -->
          <div v-else-if="candidates.length === 0" class="bg-surface-50 dark:bg-surface-900 rounded-lg p-8 text-center">
            <SparklesIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
              Mos nomzod topilmadi
            </h3>
            <p class="text-surface-600 dark:text-surface-400">
              Ushbu vakansiyaga mos keladigan nomzodlar hozircha topilmadi. Keyinroq qayta tekshiring.
            </p>
          </div>

          <!-- Candidates List -->
          <div v-else class="space-y-3">
            <!-- Summary -->
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm text-surface-500 dark:text-surface-400">
                <span class="font-semibold text-surface-900 dark:text-surface-100">{{ candidatesTotal }}</span> ta mos nomzod topildi
              </p>
            </div>

            <div
              v-for="candidate in candidates"
              :key="candidate.id"
              class="flex items-center gap-4 p-4 bg-surface-0 dark:bg-surface-900 border border-surface-200 dark:border-surface-800 rounded-xl hover:border-brand-200 dark:hover:border-brand-800 transition-colors"
            >
              <!-- Avatar -->
              <div v-if="candidate.photo_url" class="w-12 h-12 rounded-full overflow-hidden shrink-0">
                <img :src="candidate.photo_url" :alt="candidate.full_name" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 font-semibold shrink-0">
                {{ getInitials(candidate.full_name) }}
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <p class="font-medium text-surface-900 dark:text-surface-100 truncate">
                    {{ candidate.full_name || 'Noma\'lum' }}
                  </p>
                  <!-- Match Score Badge -->
                  <span
                    :class="[
                      'shrink-0 px-2 py-0.5 text-xs font-bold rounded-full',
                      getMatchScoreClasses(candidate.match_score),
                    ]"
                  >
                    {{ candidate.match_score }}%
                  </span>
                </div>
                <p class="text-sm text-surface-500 dark:text-surface-400 truncate">
                  {{ candidate.specialty || '—' }}
                  <span v-if="candidate.city"> &middot; {{ candidate.city }}</span>
                  <span v-if="candidate.experience_years"> &middot; {{ candidate.experience_years }} yil</span>
                </p>
                <!-- Skills -->
                <div v-if="candidate.skills?.length" class="flex flex-wrap gap-1 mt-1.5">
                  <span
                    v-for="skill in candidate.skills.slice(0, 5)"
                    :key="skill"
                    class="px-2 py-0.5 text-xs rounded-full bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400"
                  >
                    {{ skill }}
                  </span>
                  <span
                    v-if="candidate.skills.length > 5"
                    class="px-2 py-0.5 text-xs rounded-full bg-surface-100 dark:bg-surface-800 text-surface-500"
                  >
                    +{{ candidate.skills.length - 5 }}
                  </span>
                </div>
              </div>

              <!-- Salary -->
              <div class="hidden sm:block text-right shrink-0">
                <p v-if="candidate.expected_salary_min || candidate.expected_salary_max" class="text-sm text-surface-600 dark:text-surface-400">
                  {{ formatCandidateSalary(candidate.expected_salary_min, candidate.expected_salary_max) }}
                </p>
                <p v-if="candidate.search_status === 'passive'" class="text-xs text-warning-500 mt-0.5">
                  Passiv
                </p>
              </div>

              <!-- Save to Pool -->
              <div class="flex items-center shrink-0">
                <button
                  v-if="candidate.is_in_pool"
                  class="p-2 text-brand-500 cursor-default"
                  title="Saqlangan"
                  disabled
                >
                  <BookmarkIconSolid class="w-5 h-5" />
                </button>
                <button
                  v-else
                  class="p-2 text-surface-400 hover:text-brand-500 transition-colors"
                  title="Saqlash"
                  :disabled="savingCandidateId === candidate.id"
                  @click="saveToPool(candidate)"
                >
                  <BookmarkIcon v-if="savingCandidateId !== candidate.id" class="w-5 h-5" />
                  <div v-else class="w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full animate-spin" />
                </button>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="candidatesTotalPages > 1" class="flex justify-center pt-4">
              <AppPagination
                v-model:current-page="candidatesPage"
                :total="candidatesTotal"
                :per-page="20"
                @update:current-page="fetchRecommended"
              />
            </div>
          </div>
        </template>

        <!-- Vacancy Info Tab -->
        <template #panel-info>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
              <AppCard v-if="vacancy.description_uz || vacancy.description_ru">
                <template #header>
                  <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Tavsif</h3>
                </template>
                <div class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-line text-surface-700 dark:text-surface-300">
                  {{ vacancy.description_uz || vacancy.description_ru }}
                </div>
              </AppCard>

              <AppCard v-if="vacancy.requirements_uz || vacancy.requirements_ru">
                <template #header>
                  <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Talablar</h3>
                </template>
                <div class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-line text-surface-700 dark:text-surface-300">
                  {{ vacancy.requirements_uz || vacancy.requirements_ru }}
                </div>
              </AppCard>

              <AppCard v-if="vacancy.responsibilities_uz || vacancy.responsibilities_ru">
                <template #header>
                  <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Mas'uliyatlar</h3>
                </template>
                <div class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-line text-surface-700 dark:text-surface-300">
                  {{ vacancy.responsibilities_uz || vacancy.responsibilities_ru }}
                </div>
              </AppCard>
            </div>

            <!-- Side Info -->
            <div class="space-y-4">
              <AppCard>
                <template #header>
                  <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Ma'lumotlar</h3>
                </template>
                <div class="space-y-3">
                  <div class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Maosh</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ formatSalary(vacancy.salary_min, vacancy.salary_max, vacancy.salary_type) }}
                    </span>
                  </div>
                  <div v-if="vacancy.work_type" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Bandlik turi</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ getWorkTypeLabel(vacancy.work_type) }}
                    </span>
                  </div>
                  <div v-if="vacancy.experience_required" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Tajriba</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ vacancy.experience_required }}
                    </span>
                  </div>
                  <div v-if="vacancy.city" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Shahar</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ formatLocation(vacancy.city, vacancy.district) }}
                    </span>
                  </div>
                  <div v-if="vacancy.category" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Kategoriya</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ vacancy.category }}
                    </span>
                  </div>
                  <div v-if="vacancy.contact_phone" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Telefon</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ vacancy.contact_phone }}
                    </span>
                  </div>
                </div>
              </AppCard>

              <AppCard v-if="vacancy.published_at || vacancy.expires_at">
                <template #header>
                  <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Sanalar</h3>
                </template>
                <div class="space-y-3">
                  <div v-if="vacancy.published_at" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Chop etilgan</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ formatDate(vacancy.published_at) }}
                    </span>
                  </div>
                  <div v-if="vacancy.expires_at" class="flex justify-between text-sm">
                    <span class="text-surface-500 dark:text-surface-400">Muddati</span>
                    <span class="font-medium text-surface-900 dark:text-surface-100">
                      {{ formatDate(vacancy.expires_at) }}
                    </span>
                  </div>
                </div>
              </AppCard>
            </div>
          </div>
        </template>

        <!-- Questionnaire Tab -->
        <template #panel-questionnaire>
          <div v-if="vacancy.questionnaire">
            <AppCard>
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                    {{ vacancy.questionnaire.title }}
                  </h3>
                  <p class="text-sm text-surface-600 dark:text-surface-400 mt-1">
                    {{ vacancy.questionnaire.questions?.length || 0 }} ta savol
                  </p>
                </div>
              </div>

              <div class="space-y-4">
                <div
                  v-for="(question, index) in vacancy.questionnaire.questions"
                  :key="question.id"
                  class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                      <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-surface-500 dark:text-surface-400">
                          Savol {{ index + 1 }}
                        </span>
                        <AppBadge size="sm" variant="primary">
                          {{ getQuestionTypeLabel(question.type) }}
                        </AppBadge>
                        <AppBadge v-if="question.is_knockout" size="sm" variant="danger">
                          Knockout
                        </AppBadge>
                      </div>
                      <p class="font-medium text-surface-900 dark:text-surface-100">
                        {{ question.text_uz || question.text_ru }}
                      </p>
                    </div>
                    <div class="text-right ml-4">
                      <p class="text-sm text-surface-600 dark:text-surface-400">Og'irlik</p>
                      <p class="font-semibold text-surface-900 dark:text-surface-100">
                        {{ question.weight }}
                      </p>
                    </div>
                  </div>

                  <!-- Options for choice questions -->
                  <div v-if="question.options?.length" class="mt-3 pl-4 space-y-1">
                    <div
                      v-for="option in question.options"
                      :key="option.id"
                      class="flex items-center gap-2 text-sm"
                    >
                      <div :class="[
                        'w-1.5 h-1.5 rounded-full',
                        option.is_correct ? 'bg-success-500' : 'bg-surface-400',
                      ]" />
                      <span :class="[
                        option.is_correct
                          ? 'text-success-700 dark:text-success-400 font-medium'
                          : 'text-surface-700 dark:text-surface-300',
                      ]">
                        {{ option.label_uz || option.label_ru }}
                        <span v-if="option.is_correct && option.score" class="text-xs">({{ option.score }} ball)</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </AppCard>
          </div>
          <div v-else class="bg-surface-50 dark:bg-surface-900 rounded-lg p-8 text-center">
            <ClipboardDocumentListIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
            <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
              Savolnoma yo'q
            </h3>
            <p class="text-surface-600 dark:text-surface-400 mb-4">
              Ushbu vakansiya uchun hali savolnoma yaratilmagan
            </p>
            <AppButton variant="primary" @click="$router.push(`/dashboard/questionnaires/${vacancy.id}`)">
              Savolnoma yaratish
            </AppButton>
          </div>
        </template>
      </AppTabs>
    </div>

    <!-- Not Found -->
    <div v-else class="flex items-center justify-center py-20">
      <AppEmptyState
        title="Vakansiya topilmadi"
        description="Bunday vakansiya mavjud emas yoki o'chirilgan"
        action="Vakansiyalar ro'yxatiga qaytish"
        @action="$router.push('/dashboard/vacancies')"
      />
    </div>

    <!-- Close Confirmation Dialog -->
    <AppConfirmDialog
      :open="showCloseDialog"
      type="warning"
      title="Vakansiyani yopish"
      message="Ushbu vakansiyani yopmoqchimisiz? Yopilgan vakansiyalarga yangi arizalar kelmaydi."
      confirm-text="Yopish"
      cancel-text="Bekor qilish"
      @confirm="confirmClose"
      @cancel="showCloseDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { toast } from 'vue-sonner';
import axios from 'axios';
import { formatLocation } from '@/shared/formatters';
import {
  ArrowLeftIcon,
  PencilIcon,
  ClipboardDocumentListIcon,
  UsersIcon,
  PlayCircleIcon,
  PauseCircleIcon,
  SparklesIcon,
  BookmarkIcon,
  LockClosedIcon,
} from '@heroicons/vue/24/outline';
import { BookmarkIcon as BookmarkIconSolid } from '@heroicons/vue/24/solid';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppTabs from '../../components/ui/AppTabs.vue';
import AppDropdown from '../../components/ui/AppDropdown.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import AppEmptyState from '../../components/ui/AppEmptyState.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';
import AppPagination from '../../components/ui/AppPagination.vue';

const route = useRoute();
const router = useRouter();

const vacancy = ref(null);
const loading = ref(true);
const activeTab = ref(route.query.tab || 'pipeline');
const showCloseDialog = ref(false);

// Recommended candidates
const candidates = ref([]);
const candidatesLoading = ref(false);
const candidatesPage = ref(1);
const candidatesTotal = ref(0);
const candidatesTotalPages = ref(0);
const savingCandidateId = ref(null);
const subscriptionBlocked = ref(false);

// Applications
const applications = ref([]);
const applicationsLoading = ref(false);
const applicationsPage = ref(1);
const applicationsTotal = ref(0);
const applicationsTotalPages = ref(0);
const stageCounts = ref({});
const activeStageFilter = ref(null);

const tabs = [
  { key: 'pipeline', label: 'Arizalar' },
  { key: 'recommended', label: 'Mos nomzodlar' },
  { key: 'info', label: 'Vakansiya' },
  { key: 'questionnaire', label: 'Savolnoma' },
];

const pipelineStages = [
  { key: null, label: 'Barchasi' },
  { key: 'new', label: 'Yangi' },
  { key: 'reviewed', label: 'Ko\'rilgan' },
  { key: 'shortlisted', label: 'Tanlangan' },
  { key: 'interview', label: 'Intervyu' },
  { key: 'offered', label: 'Taklif' },
  { key: 'hired', label: 'Qabul qilindi' },
  { key: 'rejected', label: 'Rad etildi' },
  { key: 'withdrawn', label: 'Bekor qilingan' },
];

const actionMenuItems = computed(() => [
  {
    label: 'Telegram kanalda e\'lon qilish',
    icon: null,
    onClick: () => toast.info('Telegram e\'lon funksiyasi tez orada qo\'shiladi'),
  },
  {
    label: 'Havola nusxalash',
    icon: null,
    onClick: () => {
      navigator.clipboard.writeText(window.location.href);
      toast.success('Havola nusxalandi');
    },
  },
  {
    label: 'Vakansiyani yopish',
    icon: null,
    danger: true,
    onClick: () => showCloseDialog.value = true,
  },
]);

const conversionRate = computed(() => {
  if (!vacancy.value?.views_count || !vacancy.value?.applications_count) return '—';
  const rate = (vacancy.value.applications_count / vacancy.value.views_count) * 100;
  return rate.toFixed(1) + '%';
});

onMounted(async () => {
  await loadVacancy();
  if (activeTab.value === 'recommended') {
    fetchRecommended();
  }
});

// Load data when tabs change
watch(activeTab, (tab) => {
  if (tab === 'pipeline' && applications.value.length === 0) {
    fetchApplications();
  }
  if (tab === 'recommended' && candidates.value.length === 0) {
    fetchRecommended();
  }
});

async function loadVacancy() {
  loading.value = true;

  try {
    const { data } = await axios.get(`/api/recruiter/vacancies/${route.params.id}`);
    vacancy.value = data.vacancy;
    stageCounts.value = data.stage_counts || {};

    // Auto-load applications
    await fetchApplications();
  } catch (error) {
    console.error('Failed to load vacancy:', error);
    vacancy.value = null;
    if (error.response?.status === 404) {
      toast.error('Vakansiya topilmadi');
    } else {
      toast.error('Vakansiyani yuklashda xatolik');
    }
  } finally {
    loading.value = false;
  }
}

async function fetchApplications() {
  if (!vacancy.value) return;

  applicationsLoading.value = true;

  try {
    const params = {
      page: applicationsPage.value,
      per_page: 20,
    };
    if (activeStageFilter.value) {
      params.stage = activeStageFilter.value;
    }

    const { data } = await axios.get(`/api/recruiter/vacancies/${vacancy.value.id}/applications`, { params });
    applications.value = data.applications?.data || [];
    applicationsTotal.value = data.applications?.total || 0;
    applicationsTotalPages.value = data.applications?.last_page || 0;

    if (data.stage_counts) {
      stageCounts.value = data.stage_counts;
    }
  } catch (error) {
    console.error('Failed to fetch applications:', error);
  } finally {
    applicationsLoading.value = false;
  }
}

function filterByStage(stage) {
  activeStageFilter.value = stage;
  applicationsPage.value = 1;
  fetchApplications();
}

async function toggleStatus() {
  try {
    const { data } = await axios.put(`/api/recruiter/vacancies/${vacancy.value.id}/toggle-status`);
    const wasActive = vacancy.value.status === 'active';
    vacancy.value.status = data.vacancy.status;
    toast.success(wasActive ? 'Vakansiya to\'xtatildi' : 'Vakansiya faollashtirildi');
  } catch (error) {
    toast.error('Statusni o\'zgartirishda xatolik');
  }
}

function getApplicantName(app) {
  if (app.worker?.user) {
    return `${app.worker.user.first_name || ''} ${app.worker.user.last_name || ''}`.trim() || 'Noma\'lum';
  }
  return app.worker?.full_name || 'Noma\'lum';
}

function getApplicantInitials(app) {
  const name = getApplicantName(app);
  const parts = name.split(' ');
  return parts.map(p => p[0]).join('').toUpperCase().slice(0, 2) || 'N';
}

function getScoreClasses(app) {
  if (!app.knockout_passed) return 'bg-danger-100 dark:bg-danger-900/20 text-danger-600 dark:text-danger-400';
  if (app.questionnaire_score >= 80) return 'bg-success-100 dark:bg-success-900/20 text-success-600 dark:text-success-400';
  if (app.questionnaire_score >= 50) return 'bg-warning-100 dark:bg-warning-900/20 text-warning-600 dark:text-warning-400';
  return 'bg-danger-100 dark:bg-danger-900/20 text-danger-600 dark:text-danger-400';
}

function getStageVariant(stage) {
  const variants = {
    new: 'info',
    reviewed: 'warning',
    shortlisted: 'success',
    interview: 'primary',
    offered: 'success',
    hired: 'success',
    rejected: 'danger',
    withdrawn: 'default',
  };
  return variants[stage] || 'default';
}

function getStageLabel(stage) {
  const labels = {
    new: 'Yangi',
    reviewed: 'Ko\'rilgan',
    shortlisted: 'Tanlangan',
    interview: 'Intervyu',
    offered: 'Taklif',
    hired: 'Qabul qilindi',
    rejected: 'Rad etildi',
    withdrawn: 'Bekor qilingan',
  };
  return labels[stage] || stage;
}

function getStatusVariant(status) {
  const variants = {
    active: 'success',
    pending: 'warning',
    paused: 'info',
    closed: 'default',
    expired: 'default',
    draft: 'default',
  };
  return variants[status] || 'default';
}

function getStatusLabel(status) {
  const labels = {
    active: 'Faol',
    pending: 'Kutilmoqda',
    paused: 'To\'xtatilgan',
    closed: 'Yopilgan',
    expired: 'Muddati tugagan',
    draft: 'Qoralama',
  };
  return labels[status] || status;
}

function getWorkTypeLabel(type) {
  const labels = {
    full_time: 'To\'liq ish kuni',
    part_time: 'Yarim ish kuni',
    remote: 'Masofaviy',
    freelance: 'Freelance',
  };
  return labels[type] || type || '—';
}

function getQuestionTypeLabel(type) {
  const labels = {
    single_choice: 'Bir tanlov',
    multi_select: 'Ko\'p tanlov',
    number_range: 'Raqam',
    open_text: 'Ochiq javob',
    knockout: 'Knockout',
    file_upload: 'Fayl yuklash',
  };
  return labels[type] || type;
}

function formatSalary(min, max, type) {
  if (type === 'negotiable' || (!min && !max)) return 'Kelishiladi';
  const fmt = (num) => new Intl.NumberFormat('uz-UZ').format(num);
  if (min && max) return `${fmt(min)} - ${fmt(max)} so'm`;
  if (min) return `${fmt(min)}+ so'm`;
  return `${fmt(max)} so'm gacha`;
}

function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function formatRelativeDate(date) {
  if (!date) return '';
  const now = new Date();
  const d = new Date(date);
  const diff = Math.floor((now - d) / 1000);

  if (diff < 60) return 'Hozirgina';
  if (diff < 3600) return `${Math.floor(diff / 60)} min oldin`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} soat oldin`;
  if (diff < 604800) return `${Math.floor(diff / 86400)} kun oldin`;
  return formatDate(date);
}

function viewApplication(app) {
  router.push({ name: 'application-detail', params: { id: vacancy.value.id, applicationId: app.id } });
}

// ── Recommended candidates ──
async function fetchRecommended() {
  if (!vacancy.value) return;

  candidatesLoading.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/candidates/recommended', {
      params: {
        vacancy_id: vacancy.value.id,
        page: candidatesPage.value,
        per_page: 20,
      },
    });
    candidates.value = data.candidates?.data || [];
    candidatesTotal.value = data.candidates?.total || 0;
    candidatesTotalPages.value = data.candidates?.last_page || 0;
  } catch (error) {
    if (error.response?.status === 403 && error.response?.data?.limit_reached) {
      subscriptionBlocked.value = true;
    } else {
      toast.error('Nomzodlarni yuklashda xatolik');
    }
  } finally {
    candidatesLoading.value = false;
  }
}

async function saveToPool(candidate) {
  savingCandidateId.value = candidate.id;
  try {
    await axios.post('/api/recruiter/talent-pool', {
      worker_profile_id: candidate.id,
      source: 'recommendation',
    });
    candidate.is_in_pool = true;
    toast.success(`${candidate.full_name} saqlandi`);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  } finally {
    savingCandidateId.value = null;
  }
}

function getMatchScoreClasses(score) {
  if (score >= 85) return 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400';
  if (score >= 60) return 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400';
  return 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400';
}

function formatCandidateSalary(min, max) {
  const fmt = (n) => new Intl.NumberFormat('uz-UZ').format(n);
  if (min && max) return `${fmt(min)} - ${fmt(max)} so'm`;
  if (min) return `${fmt(min)}+ so'm`;
  if (max) return `${fmt(max)} so'm gacha`;
  return '';
}

function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

async function confirmClose() {
  try {
    await axios.put(`/api/recruiter/vacancies/${vacancy.value.id}`, { status: 'closed' });
    vacancy.value.status = 'closed';
    showCloseDialog.value = false;
    toast.success('Vakansiya yopildi');
  } catch (error) {
    toast.error('Xatolik yuz berdi');
  }
}
</script>
