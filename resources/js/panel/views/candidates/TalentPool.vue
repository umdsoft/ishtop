<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Nomzodlar</h1>
      <p class="mt-1 text-surface-600 dark:text-surface-400">Vakansiyalarga mos nomzodlarni toping va saqlang</p>
    </div>

    <!-- Subscription Gate -->
    <div
      v-if="featureBlocked"
      class="max-w-lg mx-auto text-center py-16"
    >
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center">
        <LockClosedIcon class="h-8 w-8 text-brand-500" />
      </div>
      <h2 class="text-xl font-bold text-surface-900 dark:text-surface-100 mb-2">
        Nomzodlar bazasi
      </h2>
      <p class="text-surface-600 dark:text-surface-400 mb-6">
        Vakansiyalarga mos nomzodlarni topish va saqlash imkoniyati faqat Recruiter Pro va undan yuqori rejalarda mavjud.
      </p>
      <router-link to="/dashboard/settings/billing">
        <AppButton variant="primary">
          Obunani yangilash
        </AppButton>
      </router-link>
    </div>

    <template v-else>
      <!-- Tabs -->
      <div class="flex border-b border-surface-200 dark:border-surface-700">
        <button
          :class="[
            'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px',
            activeTab === 'recommended'
              ? 'border-brand-500 text-brand-600 dark:text-brand-400'
              : 'border-transparent text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300',
          ]"
          @click="activeTab = 'recommended'"
        >
          <span class="flex items-center gap-2">
            <SparklesIcon class="h-4 w-4" />
            Tavsiya etilgan
          </span>
        </button>
        <button
          :class="[
            'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors -mb-px',
            activeTab === 'saved'
              ? 'border-brand-500 text-brand-600 dark:text-brand-400'
              : 'border-transparent text-surface-500 dark:text-surface-400 hover:text-surface-700 dark:hover:text-surface-300',
          ]"
          @click="activeTab = 'saved'; fetchSavedEntries()"
        >
          <span class="flex items-center gap-2">
            <BookmarkIcon class="h-4 w-4" />
            Saqlangan
            <span
              v-if="savedCount > 0"
              class="px-1.5 py-0.5 text-xs rounded-full bg-surface-200 dark:bg-surface-700 text-surface-600 dark:text-surface-400"
            >
              {{ savedCount }}
            </span>
          </span>
        </button>
      </div>

      <!-- Tab 1: Recommended -->
      <div v-if="activeTab === 'recommended'" class="space-y-4">
        <!-- Vacancy Selector -->
        <AppCard>
          <div class="flex items-center gap-3">
            <BriefcaseIcon class="h-5 w-5 text-surface-400 shrink-0" />
            <div class="flex-1">
              <AppSelect
                v-model="selectedVacancy"
                :options="vacancies"
                label-key="title_uz"
                value-key="id"
                placeholder="Vakansiya tanlang..."
                searchable
                @update:model-value="onVacancySelect"
              />
            </div>
          </div>
        </AppCard>

        <!-- No vacancy selected -->
        <AppCard v-if="!selectedVacancyId">
          <div class="text-center py-12">
            <BriefcaseIcon class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600" />
            <h3 class="mt-4 text-lg font-medium text-surface-900 dark:text-surface-100">
              Vakansiya tanlang
            </h3>
            <p class="mt-2 text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto">
              Mos nomzodlarni ko'rish uchun yuqoridagi ro'yxatdan vakansiya tanlang.
            </p>
          </div>
        </AppCard>

        <!-- Loading -->
        <div v-else-if="recommendedLoading" class="flex items-center justify-center py-12">
          <AppLoadingSpinner size="lg" text="Nomzodlar qidirilmoqda..." />
        </div>

        <!-- Empty recommended -->
        <AppCard v-else-if="recommended.length === 0">
          <div class="text-center py-12">
            <UserGroupIcon class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600" />
            <h3 class="mt-4 text-lg font-medium text-surface-900 dark:text-surface-100">
              Mos nomzod topilmadi
            </h3>
            <p class="mt-2 text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto">
              Ushbu vakansiyaga mos keladigan nomzodlar hozircha topilmadi. Keyinroq qayta tekshiring.
            </p>
          </div>
        </AppCard>

        <!-- Recommended candidates list -->
        <div v-else class="space-y-3">
          <AppCard v-for="candidate in recommended" :key="candidate.id">
            <div class="flex items-center gap-4">
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
                      getScoreClasses(candidate.match_score),
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
                <p v-if="candidate.expected_salary_min" class="text-sm text-surface-600 dark:text-surface-400">
                  {{ formatSalary(candidate.expected_salary_min, candidate.expected_salary_max) }}
                </p>
                <p v-if="candidate.search_status === 'passive'" class="text-xs text-warning-500 mt-0.5">
                  Passiv
                </p>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-1 shrink-0">
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
                  :disabled="savingId === candidate.id"
                  @click="saveToPool(candidate)"
                >
                  <BookmarkIcon v-if="savingId !== candidate.id" class="w-5 h-5" />
                  <div v-else class="w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full animate-spin" />
                </button>
              </div>
            </div>
          </AppCard>

          <!-- Pagination -->
          <div v-if="recommendedTotal > recommendedPerPage" class="pt-2">
            <AppPagination
              v-model:current-page="recommendedPage"
              :total="recommendedTotal"
              :per-page="recommendedPerPage"
              @update:current-page="fetchRecommended"
            />
          </div>
        </div>
      </div>

      <!-- Tab 2: Saved -->
      <div v-if="activeTab === 'saved'" class="space-y-4">
        <!-- Search -->
        <AppCard>
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
              <AppInput
                v-model="savedSearch"
                type="text"
                placeholder="Ism yoki mutaxassislik bo'yicha qidirish..."
                @keyup.enter="fetchSavedEntries"
              />
            </div>
            <AppButton variant="primary" @click="fetchSavedEntries">
              Qidirish
            </AppButton>
          </div>
        </AppCard>

        <!-- Loading -->
        <div v-if="savedLoading" class="flex items-center justify-center py-12">
          <AppLoadingSpinner text="Yuklanmoqda..." />
        </div>

        <!-- Empty saved -->
        <AppCard v-else-if="savedEntries.length === 0">
          <div class="text-center py-12">
            <BookmarkIcon class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600" />
            <h3 class="mt-4 text-lg font-medium text-surface-900 dark:text-surface-100">
              Saqlangan nomzodlar yo'q
            </h3>
            <p class="mt-2 text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto">
              Tavsiya etilgan nomzodlardan saqlang — ular shu yerda ko'rinadi.
            </p>
          </div>
        </AppCard>

        <!-- Saved entries list -->
        <div v-else class="space-y-3">
          <AppCard v-for="entry in savedEntries" :key="entry.id">
            <div class="flex items-center gap-4">
              <!-- Avatar -->
              <div v-if="entry.worker_profile?.photo_url" class="w-12 h-12 rounded-full overflow-hidden shrink-0">
                <img :src="entry.worker_profile.photo_url" :alt="entry.worker_profile?.full_name" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 font-semibold shrink-0">
                {{ getInitials(entry.worker_profile?.full_name) }}
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <p class="font-medium text-surface-900 dark:text-surface-100 truncate">
                  {{ entry.worker_profile?.full_name || 'Noma\'lum' }}
                </p>
                <p class="text-sm text-surface-500 dark:text-surface-400 truncate">
                  {{ entry.worker_profile?.specialty || '—' }}
                  <span v-if="entry.worker_profile?.city"> &middot; {{ entry.worker_profile.city }}</span>
                </p>
                <div v-if="entry.tags?.length" class="flex flex-wrap gap-1 mt-1">
                  <span
                    v-for="tag in entry.tags"
                    :key="tag"
                    class="px-2 py-0.5 text-xs rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>

              <!-- Details -->
              <div class="hidden sm:block text-right shrink-0">
                <p v-if="entry.worker_profile?.experience_years" class="text-sm text-surface-600 dark:text-surface-400">
                  {{ entry.worker_profile.experience_years }} yil tajriba
                </p>
                <p v-if="entry.worker_profile?.expected_salary_min" class="text-xs text-surface-500 dark:text-surface-400">
                  {{ formatSalary(entry.worker_profile.expected_salary_min, entry.worker_profile.expected_salary_max) }}
                </p>
              </div>

              <!-- Remove -->
              <button
                @click="removeEntry(entry)"
                class="p-2 text-surface-400 hover:text-danger-500 dark:hover:text-danger-400 transition-colors shrink-0"
                title="Olib tashlash"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>

            <!-- Notes -->
            <div v-if="entry.notes" class="mt-3 pt-3 border-t border-surface-100 dark:border-surface-800">
              <p class="text-sm text-surface-600 dark:text-surface-400">{{ entry.notes }}</p>
            </div>
          </AppCard>

          <!-- Pagination -->
          <div v-if="savedTotalPages > 1" class="flex justify-center gap-2 pt-4">
            <AppButton
              variant="outline"
              size="sm"
              :disabled="savedPage <= 1"
              @click="savedPage--; fetchSavedEntries()"
            >
              Oldingi
            </AppButton>
            <span class="flex items-center px-3 text-sm text-surface-600 dark:text-surface-400">
              {{ savedPage }} / {{ savedTotalPages }}
            </span>
            <AppButton
              variant="outline"
              size="sm"
              :disabled="savedPage >= savedTotalPages"
              @click="savedPage++; fetchSavedEntries()"
            >
              Keyingi
            </AppButton>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { formatSalary } from '@/shared/formatters';
import {
  SparklesIcon,
  BookmarkIcon,
  BriefcaseIcon,
  TrashIcon,
  UserGroupIcon,
  LockClosedIcon,
} from '@heroicons/vue/24/outline';
import { BookmarkIcon as BookmarkIconSolid } from '@heroicons/vue/24/solid';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppSelect from '../../components/ui/AppSelect.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import AppPagination from '../../components/ui/AppPagination.vue';

// ── State ──
const activeTab = ref('recommended');
const featureBlocked = ref(false);

// Recommended tab
const vacancies = ref([]);
const selectedVacancy = ref(null);
const selectedVacancyId = ref(null);
const recommended = ref([]);
const recommendedLoading = ref(false);
const recommendedPage = ref(1);
const recommendedPerPage = ref(20);
const recommendedTotal = ref(0);
const savingId = ref(null);

// Saved tab
const savedEntries = ref([]);
const savedLoading = ref(false);
const savedSearch = ref('');
const savedPage = ref(1);
const savedTotalPages = ref(1);
const savedCount = ref(0);

// ── Init ──
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/recruiter/candidates/vacancies');
    vacancies.value = data.vacancies || [];
  } catch (error) {
    if (error.response?.status === 403 && error.response?.data?.limit_reached) {
      featureBlocked.value = true;
      return;
    }
  }

  // Load saved count
  try {
    const { data } = await axios.get('/api/recruiter/talent-pool', { params: { per_page: 1 } });
    savedCount.value = data.total || 0;
  } catch {
    // ignore
  }
});

// ── Recommended ──
function onVacancySelect(val) {
  const id = val && typeof val === 'object' ? val.id : val;
  selectedVacancyId.value = id;
  recommendedPage.value = 1;
  if (id) {
    fetchRecommended();
  }
}

async function fetchRecommended() {
  if (!selectedVacancyId.value) return;

  recommendedLoading.value = true;
  try {
    const { data } = await axios.get('/api/recruiter/candidates/recommended', {
      params: {
        vacancy_id: selectedVacancyId.value,
        page: recommendedPage.value,
        per_page: recommendedPerPage.value,
      },
    });
    recommended.value = data.candidates?.data || [];
    recommendedTotal.value = data.candidates?.total || 0;
  } catch (error) {
    if (error.response?.status === 403 && error.response?.data?.limit_reached) {
      featureBlocked.value = true;
    } else {
      toast.error('Nomzodlarni yuklashda xatolik');
    }
  } finally {
    recommendedLoading.value = false;
  }
}

async function saveToPool(candidate) {
  savingId.value = candidate.id;
  try {
    await axios.post('/api/recruiter/talent-pool', {
      worker_profile_id: candidate.id,
      source: 'recommendation',
    });
    candidate.is_in_pool = true;
    savedCount.value++;
    toast.success(`${candidate.full_name} saqlandi`);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  } finally {
    savingId.value = null;
  }
}

// ── Saved ──
async function fetchSavedEntries() {
  savedLoading.value = true;
  try {
    const params = { page: savedPage.value };
    if (savedSearch.value) params.q = savedSearch.value;

    const { data } = await axios.get('/api/recruiter/talent-pool', { params });
    savedEntries.value = data.data || [];
    savedTotalPages.value = data.last_page || 1;
    savedCount.value = data.total || 0;
  } catch {
    toast.error('Saqlangan nomzodlarni yuklashda xatolik');
  } finally {
    savedLoading.value = false;
  }
}

async function removeEntry(entry) {
  try {
    await axios.delete(`/api/recruiter/talent-pool/${entry.id}`);
    savedEntries.value = savedEntries.value.filter(e => e.id !== entry.id);
    savedCount.value = Math.max(0, savedCount.value - 1);
    toast.success('Nomzod olib tashlandi');
  } catch {
    toast.error('Xatolik yuz berdi');
  }
}

// ── Helpers ──
function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function getScoreClasses(score) {
  if (score >= 85) return 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400';
  if (score >= 60) return 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400';
  return 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400';
}
</script>
