<template>
  <div>
    <!-- Loading State -->
    <div v-if="loadingVacancy" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Vakansiya yuklanmoqda..." />
    </div>

    <!-- Form -->
    <div v-else>
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
          <button
            class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
            @click="$router.push('/dashboard/vacancies')"
          >
            <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
          </button>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Vakansiyani tahrirlash</h1>
        </div>
        <p class="text-surface-600 dark:text-surface-400 ml-14">{{ form.title }}</p>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Form -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Language Selector -->
            <div class="flex items-center gap-3 p-4 bg-surface-50 dark:bg-surface-800/50 rounded-xl border border-surface-200 dark:border-surface-700">
              <LanguageIcon class="h-5 w-5 text-surface-500 dark:text-surface-400" />
              <span class="text-sm font-medium text-surface-700 dark:text-surface-300">E'lon tili:</span>
              <div class="flex rounded-lg border border-surface-200 dark:border-surface-700 overflow-hidden">
                <button
                  type="button"
                  :class="[
                    'px-4 py-1.5 text-sm font-medium transition-colors',
                    form.language === 'uz'
                      ? 'bg-brand-500 text-white'
                      : 'bg-surface-0 dark:bg-surface-800 text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700'
                  ]"
                  @click="form.language = 'uz'"
                >
                  O'zbekcha
                </button>
                <button
                  type="button"
                  :class="[
                    'px-4 py-1.5 text-sm font-medium transition-colors',
                    form.language === 'ru'
                      ? 'bg-brand-500 text-white'
                      : 'bg-surface-0 dark:bg-surface-800 text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700'
                  ]"
                  @click="form.language = 'ru'"
                >
                  Ruscha
                </button>
              </div>
            </div>

            <!-- Basic Information -->
            <AppCard>
              <template #header>
                <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Asosiy ma'lumotlar</h2>
              </template>

              <div class="space-y-4">
                <AppInput
                  v-model="form.title"
                  :label="form.language === 'uz' ? 'Vakansiya nomi' : 'Название вакансии'"
                  placeholder="Senior PHP Developer"
                  required
                  :error="errors.title"
                />

                <AppSelect
                  v-model="form.category_id"
                  :options="categories"
                  label="Kategoriya"
                  label-key="name"
                  value-key="id"
                  placeholder="Kategoriya tanlang"
                  searchable
                  required
                  :error="errors.category_id"
                />

                <div class="grid grid-cols-2 gap-4">
                  <AppSelect
                    v-model="form.region_id"
                    :options="regions"
                    label="Viloyat"
                    label-key="name"
                    value-key="id"
                    placeholder="Viloyatni tanlang"
                    searchable
                    required
                    :error="errors.region_id"
                  />

                  <AppSelect
                    v-model="form.district_id"
                    :options="filteredDistricts"
                    label="Tuman/Shahar"
                    label-key="name"
                    value-key="id"
                    placeholder="Tumanni tanlang"
                    searchable
                    :disabled="!form.region_id"
                    :error="errors.district_id"
                  />
                </div>

                <AppInput
                  v-model="form.address"
                  label="Manzil"
                  placeholder="Amir Temur ko'chasi, 15-uy"
                  :error="errors.address"
                />

                <AppTextarea
                  v-model="form.description"
                  :label="form.language === 'uz' ? 'Tavsif' : 'Описание'"
                  :placeholder="form.language === 'uz' ? 'Vakansiya haqida batafsil ma\'lumot...' : 'Подробная информация о вакансии...'"
                  :rows="6"
                  :maxlength="2000"
                  required
                  :error="errors.description"
                />
              </div>
            </AppCard>

            <!-- Employment Details -->
            <AppCard>
              <template #header>
                <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Bandlik ma'lumotlari</h2>
              </template>

              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <AppSelect
                    v-model="form.employment_type"
                    :options="employmentTypes"
                    label="Bandlik turi"
                    label-key="label"
                    value-key="value"
                    placeholder="Tanlang"
                    required
                    :error="errors.employment_type"
                  />

                  <AppSelect
                    v-model="form.experience_level"
                    :options="experienceLevels"
                    label="Tajriba darajasi"
                    label-key="label"
                    value-key="value"
                    placeholder="Tanlang"
                    required
                    :error="errors.experience_level"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-3">
                    Maosh
                  </label>
                  <div class="space-y-3">
                    <div class="flex items-center gap-2">
                      <AppCheckbox v-model="form.salary_negotiable" label="Kelishiladi" />
                    </div>

                    <div v-if="!form.salary_negotiable" class="grid grid-cols-2 gap-4">
                      <AppInput
                        v-model.number="form.salary_min"
                        type="number"
                        label="Minimal (so'm)"
                        placeholder="3000000"
                        :error="errors.salary_min"
                      />

                      <AppInput
                        v-model.number="form.salary_max"
                        type="number"
                        label="Maksimal (so'm)"
                        placeholder="5000000"
                        :error="errors.salary_max"
                      />
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <AppInput
                    v-model.number="form.max_applicants"
                    type="number"
                    label="Maksimal arizalar soni"
                    placeholder="100"
                    hint="0 = cheksiz"
                    :error="errors.max_applicants"
                  />

                  <AppDatePicker
                    v-model="form.deadline"
                    label="Muddati"
                    :min="minDeadline"
                    hint="Arizalar qabul qilish muddati"
                    :error="errors.deadline"
                  />
                </div>
              </div>
            </AppCard>

            <!-- Requirements & Responsibilities -->
            <AppCard>
              <template #header>
                <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Talablar va mas'uliyatlar</h2>
              </template>

              <div class="space-y-4">
                <AppTextarea
                  v-model="form.requirements"
                  :label="form.language === 'uz' ? 'Talablar' : 'Требования'"
                  :placeholder="form.language === 'uz' ? '- Kamida 3 yillik tajriba\n- PHP va Laravel bilishi\n- MySQL bilishi' : '- Минимум 3 года опыта\n- Знание PHP и Laravel\n- Знание MySQL'"
                  :rows="5"
                  :hint="form.language === 'uz' ? 'Har bir talabni yangi qatordan boshlang' : 'Каждое требование с новой строки'"
                  :error="errors.requirements"
                />

                <AppTextarea
                  v-model="form.responsibilities"
                  :label="form.language === 'uz' ? 'Mas\'uliyatlar' : 'Обязанности'"
                  :placeholder="form.language === 'uz' ? '- Backend dasturlash\n- API yaratish\n- Database optimallashtirish' : '- Backend разработка\n- Создание API\n- Оптимизация базы данных'"
                  :rows="5"
                  :hint="form.language === 'uz' ? 'Har bir mas\'uliyatni yangi qatordan boshlang' : 'Каждую обязанность с новой строки'"
                  :error="errors.responsibilities"
                />

                <AppTextarea
                  v-model="form.benefits"
                  label="Imtiyozlar (ixtiyoriy)"
                  placeholder="- Bepul tushlik&#10;- Sog'liqni saqlash sug'urtasi&#10;- Kasbiy rivojlanish"
                  :rows="4"
                  hint="Har bir imtiyozni yangi qatordan boshlang"
                  :error="errors.benefits"
                />
              </div>
            </AppCard>

            <!-- Translation Section -->
            <AppCard>
              <button
                type="button"
                class="w-full flex items-center justify-between py-1"
                @click="showTranslation = !showTranslation"
              >
                <div class="flex items-center gap-3">
                  <LanguageIcon class="h-5 w-5 text-brand-500" />
                  <div class="text-left">
                    <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">{{ translationLabel }}</h2>
                    <p class="text-xs text-surface-500 dark:text-surface-400">Ixtiyoriy — tarjimani keyinroq ham qo'shish mumkin</p>
                  </div>
                </div>
                <ChevronDownIcon
                  :class="['h-5 w-5 text-surface-400 transition-transform duration-200', showTranslation ? 'rotate-180' : '']"
                />
              </button>

              <div v-if="showTranslation" class="mt-4 space-y-4 pt-4 border-t border-surface-200 dark:border-surface-700">
                <!-- AI Translate Button -->
                <button
                  type="button"
                  :disabled="translating || (!form.title && !form.description)"
                  class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white shadow-sm hover:shadow-md"
                  @click="handleAiTranslate"
                >
                  <SparklesIcon v-if="!translating" class="h-4 w-4" />
                  <svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ translating ? 'Tarjima qilinmoqda...' : 'AI bilan tarjima qilish' }}
                </button>

                <AppInput
                  v-model="form.title_translation"
                  :label="form.language === 'uz' ? 'Название вакансии' : 'Vakansiya nomi'"
                  :placeholder="form.language === 'uz' ? 'Senior PHP Developer' : 'Senior PHP Developer'"
                  :error="errors.title_translation"
                />

                <AppTextarea
                  v-model="form.description_translation"
                  :label="form.language === 'uz' ? 'Описание' : 'Tavsif'"
                  :placeholder="form.language === 'uz' ? 'Подробная информация о вакансии...' : 'Vakansiya haqida batafsil ma\'lumot...'"
                  :rows="5"
                  :error="errors.description_translation"
                />

                <AppTextarea
                  v-model="form.requirements_translation"
                  :label="form.language === 'uz' ? 'Требования' : 'Talablar'"
                  :rows="4"
                  :error="errors.requirements_translation"
                />

                <AppTextarea
                  v-model="form.responsibilities_translation"
                  :label="form.language === 'uz' ? 'Обязанности' : 'Mas\'uliyatlar'"
                  :rows="4"
                  :error="errors.responsibilities_translation"
                />
              </div>
            </AppCard>

            <!-- Contact Information -->
            <AppCard>
              <template #header>
                <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Kontakt ma'lumotlari</h2>
              </template>

              <div class="space-y-4">
                <AppInput
                  v-model="form.contact_name"
                  label="Kontakt shaxs"
                  placeholder="Umidbek Karimov"
                  required
                  :error="errors.contact_name"
                />

                <div class="grid grid-cols-2 gap-4">
                  <AppInput
                    v-model="form.contact_phone"
                    label="Telefon raqami"
                    type="tel"
                    placeholder="+998 90 123 45 67"
                    required
                    :error="errors.contact_phone"
                  />

                  <AppInput
                    v-model="form.contact_email"
                    label="Email"
                    type="email"
                    placeholder="hr@company.com"
                    :error="errors.contact_email"
                  />
                </div>
              </div>
            </AppCard>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Actions -->
            <AppCard>
              <div class="space-y-3">
                <AppButton
                  type="submit"
                  variant="primary"
                  size="lg"
                  full-width
                  :loading="loading"
                >
                  O'zgarishlarni saqlash
                </AppButton>

                <AppButton
                  type="button"
                  variant="ghost"
                  size="lg"
                  full-width
                  :disabled="loading"
                  @click="$router.push('/dashboard/vacancies')"
                >
                  Bekor qilish
                </AppButton>
              </div>
            </AppCard>

            <!-- Settings -->
            <AppCard>
              <template #header>
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Sozlamalar</h3>
              </template>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                    Status
                  </label>
                  <AppSelect
                    v-model="form.status"
                    :options="statusOptions"
                    label-key="label"
                    value-key="value"
                  />
                </div>

                <div class="space-y-3">
                  <AppCheckbox
                    v-model="form.is_featured"
                    label="Tavsiya etilgan"
                    description="Vakansiyani bosh sahifada ko'rsatish"
                  />

                  <AppCheckbox
                    v-model="form.auto_reject_unqualified"
                    label="Avtomatik rad etish"
                    description="Minimal ballga yetmaganlarni avtomatik rad etish"
                  />

                  <AppCheckbox
                    v-model="form.require_questionnaire"
                    label="Savolnoma majburiy"
                    description="Arizachilar savolnomaga javob berishlari shart"
                  />
                </div>
              </div>
            </AppCard>

            <!-- Stats -->
            <AppCard v-if="vacancy">
              <template #header>
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Statistika</h3>
              </template>

              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-surface-600 dark:text-surface-400">Arizalar</span>
                  <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                    {{ vacancy.applications_count }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-surface-600 dark:text-surface-400">Ko'rilgan</span>
                  <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                    {{ vacancy.views_count }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-surface-600 dark:text-surface-400">Yaratilgan</span>
                  <span class="text-sm font-medium text-surface-900 dark:text-surface-100">
                    {{ formatDate(vacancy.created_at) }}
                  </span>
                </div>
              </div>
            </AppCard>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { ArrowLeftIcon, ChevronDownIcon, LanguageIcon, SparklesIcon } from '@heroicons/vue/24/outline';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppTextarea from '../../components/ui/AppTextarea.vue';
import AppSelect from '../../components/ui/AppSelect.vue';
import AppCheckbox from '../../components/ui/AppCheckbox.vue';
import AppDatePicker from '../../components/ui/AppDatePicker.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import { regions, districts } from '../../data/regions.js';

const route = useRoute();
const router = useRouter();

const vacancy = ref(null);
const loadingVacancy = ref(true);

const form = ref({
  language: 'uz',
  title: '',
  category_id: null,
  region_id: null,
  district_id: null,
  address: '',
  description: '',
  employment_type: 'full_time',
  experience_level: 'mid',
  salary_negotiable: false,
  salary_min: null,
  salary_max: null,
  max_applicants: 0,
  deadline: null,
  requirements: '',
  responsibilities: '',
  benefits: '',
  contact_name: '',
  contact_phone: '',
  contact_email: '',
  status: 'draft',
  is_featured: false,
  auto_reject_unqualified: false,
  require_questionnaire: true,
  // Translation fields
  title_translation: '',
  description_translation: '',
  requirements_translation: '',
  responsibilities_translation: '',
});

const showTranslation = ref(false);
const translating = ref(false);
const translationLabel = computed(() => {
  return form.value.language === 'uz' ? 'Rus tiliga tarjima' : "O'zbek tiliga tarjima";
});

const errors = ref({});
const loading = ref(false);

const categories = ref([
  { id: 1, name: 'IT va Texnologiya' },
  { id: 2, name: 'Savdo va Marketing' },
  { id: 3, name: 'Moliya va Buxgalteriya' },
  { id: 4, name: 'Qurilish' },
  { id: 5, name: 'Ovqatlanish' },
  { id: 6, name: 'Transport' },
  { id: 7, name: 'Ta\'lim' },
  { id: 8, name: 'Sog\'liqni saqlash' },
]);

// regions va districts are imported from ../../data/regions.js

const employmentTypes = [
  { value: 'full_time', label: 'To\'liq ish kuni' },
  { value: 'part_time', label: 'Yarim ish kuni' },
  { value: 'remote', label: 'Masofaviy' },
  { value: 'freelance', label: 'Freelance' },
];

const experienceLevels = [
  { value: 'entry', label: 'Boshlang\'ich (0-1 yil)' },
  { value: 'junior', label: 'Junior (1-3 yil)' },
  { value: 'mid', label: 'Middle (3-5 yil)' },
  { value: 'senior', label: 'Senior (5+ yil)' },
];

const statusOptions = [
  { value: 'draft', label: 'Qoralama' },
  { value: 'pending', label: 'Kutilmoqda' },
  { value: 'active', label: 'Faol' },
  { value: 'closed', label: 'Yopilgan' },
];

const filteredDistricts = computed(() => {
  const regionId = form.value.region_id && typeof form.value.region_id === 'object'
    ? form.value.region_id.id
    : form.value.region_id;
  if (!regionId) return [];
  return districts.filter(d => d.region_id === regionId);
});

const minDeadline = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

onMounted(async () => {
  await loadVacancy();
});

async function loadVacancy() {
  loadingVacancy.value = true;

  try {
    const response = await axios.get(`/api/recruiter/vacancies/${route.params.id}`);
    vacancy.value = response.data.vacancy;

    const v = vacancy.value;
    const lang = v.language || 'uz';
    const otherLang = lang === 'uz' ? 'ru' : 'uz';

    // Map bilingual fields to primary/translation
    form.value.language = lang;
    form.value.title = v[`title_${lang}`] || v.title_uz || '';
    form.value.description = v[`description_${lang}`] || v.description_uz || '';
    form.value.requirements = v[`requirements_${lang}`] || v.requirements_uz || '';
    form.value.responsibilities = v[`responsibilities_${lang}`] || v.responsibilities_uz || '';
    form.value.title_translation = v[`title_${otherLang}`] || '';
    form.value.description_translation = v[`description_${otherLang}`] || '';
    form.value.requirements_translation = v[`requirements_${otherLang}`] || '';
    form.value.responsibilities_translation = v[`responsibilities_${otherLang}`] || '';

    // Show translation section if translations exist
    if (form.value.title_translation || form.value.description_translation) {
      showTranslation.value = true;
    }

    // Non-bilingual fields
    form.value.address = v.address || '';
    form.value.salary_negotiable = v.salary_type === 'negotiable';
    form.value.salary_min = v.salary_min;
    form.value.salary_max = v.salary_max;
    form.value.max_applicants = v.max_applicants || 0;
    form.value.deadline = v.expires_at ? v.expires_at.split('T')[0] : null;
    form.value.benefits = v.benefits || '';
    form.value.contact_name = v.contact_name || '';
    form.value.contact_phone = v.contact_phone || '';
    form.value.contact_email = v.contact_email || '';
    form.value.is_featured = v.is_featured || false;
    form.value.auto_reject_unqualified = v.auto_reject_unqualified || false;
    form.value.require_questionnaire = v.require_questionnaire ?? true;

    // Map raw string values to option objects for AppSelect
    form.value.employment_type = employmentTypes.find(t => t.value === v.work_type) || employmentTypes[0];
    form.value.experience_level = experienceLevels.find(t => t.value === v.experience_required) || experienceLevels[2];
    form.value.status = statusOptions.find(t => t.value === v.status) || statusOptions[0];
    form.value.category_id = categories.value.find(c => c.id === v.category_id) || null;
    form.value.region_id = regions.find(r => r.id === v.region_id) || null;
    form.value.district_id = districts.find(d => d.id === v.district_id) || null;
  } catch (error) {
    toast.error('Vakansiya yuklanmadi');
    router.push('/dashboard/vacancies');
  } finally {
    loadingVacancy.value = false;
  }
}

function getFormData() {
  const data = { ...form.value };
  const lang = data.language || 'uz';
  const otherLang = lang === 'uz' ? 'ru' : 'uz';

  // Map primary content and translation to _uz/_ru fields
  data[`title_${lang}`] = data.title;
  data[`description_${lang}`] = data.description;
  data[`requirements_${lang}`] = data.requirements || null;
  data[`responsibilities_${lang}`] = data.responsibilities || null;
  data[`title_${otherLang}`] = data.title_translation || null;
  data[`description_${otherLang}`] = data.description_translation || null;
  data[`requirements_${otherLang}`] = data.requirements_translation || null;
  data[`responsibilities_${otherLang}`] = data.responsibilities_translation || null;

  // Clean up non-API fields
  delete data.title;
  delete data.description;
  delete data.requirements;
  delete data.responsibilities;
  delete data.title_translation;
  delete data.description_translation;
  delete data.requirements_translation;
  delete data.responsibilities_translation;

  // Extract .value from option objects for AppSelect fields
  if (data.employment_type && typeof data.employment_type === 'object') {
    data.employment_type = data.employment_type.value;
  }
  if (data.experience_level && typeof data.experience_level === 'object') {
    data.experience_level = data.experience_level.value;
  }
  if (data.status && typeof data.status === 'object') {
    data.status = data.status.value;
  }
  if (data.category_id && typeof data.category_id === 'object') {
    data.category_id = data.category_id.id;
  }
  if (data.region_id && typeof data.region_id === 'object') {
    data.region_id = data.region_id.id;
  }
  if (data.district_id && typeof data.district_id === 'object') {
    data.district_id = data.district_id.id;
  }
  return data;
}

async function handleSubmit() {
  errors.value = {};
  loading.value = true;

  // Validate
  if (!validateForm()) {
    loading.value = false;
    return;
  }

  try {
    const data = getFormData();
    await axios.put(`/api/recruiter/vacancies/${route.params.id}`, data);

    toast.success('O\'zgarishlar saqlandi!');
    router.push('/dashboard/vacancies');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi. Qaytadan urinib ko\'ring.');
  } finally {
    loading.value = false;
  }
}

async function handleAiTranslate() {
  if (!form.value.title && !form.value.description) {
    toast.error('Tarjima qilish uchun avval asosiy matnni yozing');
    return;
  }

  translating.value = true;
  try {
    const from = form.value.language || 'uz';
    const to = from === 'uz' ? 'ru' : 'uz';

    const { data } = await axios.post('/api/recruiter/vacancies/translate', {
      from,
      to,
      title: form.value.title || null,
      description: form.value.description || null,
      requirements: form.value.requirements || null,
      responsibilities: form.value.responsibilities || null,
    });

    if (data.translated) {
      if (data.translated.title) form.value.title_translation = data.translated.title;
      if (data.translated.description) form.value.description_translation = data.translated.description;
      if (data.translated.requirements) form.value.requirements_translation = data.translated.requirements;
      if (data.translated.responsibilities) form.value.responsibilities_translation = data.translated.responsibilities;
      toast.success('Tarjima muvaffaqiyatli bajarildi!');
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Tarjima xatoligi. Qaytadan urinib ko\'ring.');
  } finally {
    translating.value = false;
  }
}

function validateForm() {
  const newErrors = {};

  if (!form.value.title) {
    newErrors.title = 'Vakansiya nomi kiritilishi shart';
  }

  if (!form.value.category_id) {
    newErrors.category_id = 'Kategoriya tanlanishi shart';
  }

  if (!form.value.region_id) {
    newErrors.region_id = 'Viloyat tanlanishi shart';
  }

  if (!form.value.description) {
    newErrors.description = 'Tavsif kiritilishi shart';
  }

  if (!form.value.contact_name) {
    newErrors.contact_name = 'Kontakt shaxs kiritilishi shart';
  }

  if (!form.value.contact_phone) {
    newErrors.contact_phone = 'Telefon raqami kiritilishi shart';
  }

  if (!form.value.salary_negotiable) {
    if (form.value.salary_min && form.value.salary_max && form.value.salary_min > form.value.salary_max) {
      newErrors.salary_min = 'Minimal maosh maksimaldan katta bo\'lmasligi kerak';
    }
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}
</script>
