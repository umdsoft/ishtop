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
              <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Qaysi tilda yozasiz:</span>
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
              <span class="text-xs text-surface-400 dark:text-surface-500 ml-auto hidden sm:inline">
                Ikkinchi tilga avtomatik tarjima qilinadi
              </span>
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
                  <template v-if="loadingPhase === 'translating'">
                    <span class="flex items-center gap-2">
                      <SparklesIcon class="h-4 w-4 animate-pulse" />
                      Tarjima qilinmoqda...
                    </span>
                  </template>
                  <template v-else-if="loadingPhase === 'saving'">
                    Saqlanmoqda...
                  </template>
                  <template v-else>
                    O'zgarishlarni saqlash
                  </template>
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
import { ArrowLeftIcon, LanguageIcon, SparklesIcon } from '@heroicons/vue/24/outline';
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
const canTranslate = ref(false);

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
});

const errors = ref({});
const loading = ref(false);
const loadingPhase = ref(''); // 'translating' | 'saving' | ''

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
  try {
    const { data } = await axios.get('/api/subscriptions/current');
    canTranslate.value = !!data.limits?.ai_translation;
  } catch {
    // Continue even if check fails
  }
  await loadVacancy();
});

async function loadVacancy() {
  loadingVacancy.value = true;

  try {
    const response = await axios.get(`/api/recruiter/vacancies/${route.params.id}`);
    vacancy.value = response.data.vacancy;

    const v = vacancy.value;
    const lang = v.language || 'uz';

    // Map bilingual fields to primary form fields based on language
    form.value.language = lang;
    form.value.title = v[`title_${lang}`] || v.title_uz || v.title_ru || '';
    form.value.description = v[`description_${lang}`] || v.description_uz || v.description_ru || '';
    form.value.requirements = v[`requirements_${lang}`] || v.requirements_uz || v.requirements_ru || '';
    form.value.responsibilities = v[`responsibilities_${lang}`] || v.responsibilities_uz || v.responsibilities_ru || '';

    // Non-bilingual fields
    form.value.salary_negotiable = v.salary_type === 'negotiable';
    form.value.salary_min = v.salary_min;
    form.value.salary_max = v.salary_max;
    form.value.contact_phone = v.contact_phone || '';

    // Map raw string values to option objects for AppSelect
    form.value.employment_type = employmentTypes.find(t => t.value === v.work_type) || employmentTypes[0];
    form.value.experience_level = experienceLevels.find(t => t.value === v.experience_required) || experienceLevels[2];
    form.value.status = statusOptions.find(t => t.value === v.status) || statusOptions[0];
    form.value.category_id = categories.value.find(c => c.name === v.category) || null;
    form.value.region_id = regions.find(r => r.name === v.city) || null;
    form.value.district_id = districts.find(d => d.name === v.district) || null;
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

  // Map primary fields to the selected language columns
  data[`title_${lang}`] = data.title;
  data[`description_${lang}`] = data.description;
  data[`requirements_${lang}`] = data.requirements || null;
  data[`responsibilities_${lang}`] = data.responsibilities || null;

  // Clean up generic field names
  delete data.title;
  delete data.description;
  delete data.requirements;
  delete data.responsibilities;

  // Map category_id to category (backend expects string name)
  if (data.category_id) {
    const cat = typeof data.category_id === 'object'
      ? data.category_id
      : categories.value.find(c => c.id === data.category_id);
    data.category = cat?.name || '';
  }
  delete data.category_id;

  // Map employment_type to work_type (backend field name)
  if (data.employment_type && typeof data.employment_type === 'object') {
    data.work_type = data.employment_type.value;
  } else {
    data.work_type = data.employment_type;
  }
  delete data.employment_type;

  // Map experience_level to experience_required (backend field name)
  if (data.experience_level && typeof data.experience_level === 'object') {
    data.experience_required = data.experience_level.value;
  } else {
    data.experience_required = data.experience_level;
  }
  delete data.experience_level;

  // Map region_id to city (backend expects string name)
  if (data.region_id) {
    const region = typeof data.region_id === 'object'
      ? data.region_id
      : regions.find(r => r.id === data.region_id);
    data.city = region?.name || '';
  }
  delete data.region_id;

  // Map district_id to district (backend expects string name)
  if (data.district_id) {
    const dist = typeof data.district_id === 'object'
      ? data.district_id
      : districts.find(d => d.id === data.district_id);
    data.district = dist?.name || '';
  }
  delete data.district_id;

  // Map salary_negotiable to salary_type
  if (data.salary_negotiable) {
    data.salary_type = 'negotiable';
    data.salary_min = null;
    data.salary_max = null;
  } else if (data.salary_min && data.salary_max) {
    data.salary_type = 'range';
  } else if (data.salary_min) {
    data.salary_type = 'fixed';
  }
  delete data.salary_negotiable;

  // Map status from object
  if (data.status && typeof data.status === 'object') {
    data.status = data.status.value;
  }

  // Remove fields not in backend schema
  delete data.contact_name;
  delete data.contact_email;
  delete data.max_applicants;
  delete data.deadline;
  delete data.is_featured;
  delete data.auto_reject_unqualified;
  delete data.require_questionnaire;
  delete data.benefits;
  delete data.address;

  return data;
}

async function autoTranslate(data) {
  if (!canTranslate.value) return data;

  const lang = data.language || 'uz';
  const otherLang = lang === 'uz' ? 'ru' : 'uz';

  const fields = {};
  if (data[`title_${lang}`]) fields.title = data[`title_${lang}`];
  if (data[`description_${lang}`]) fields.description = data[`description_${lang}`];
  if (data[`requirements_${lang}`]) fields.requirements = data[`requirements_${lang}`];
  if (data[`responsibilities_${lang}`]) fields.responsibilities = data[`responsibilities_${lang}`];

  if (Object.keys(fields).length === 0) return data;

  try {
    loadingPhase.value = 'translating';
    const { data: result } = await axios.post('/api/recruiter/vacancies/translate', {
      from: lang,
      to: otherLang,
      ...fields,
    });

    if (result.translated) {
      if (result.translated.title) data[`title_${otherLang}`] = result.translated.title;
      if (result.translated.description) data[`description_${otherLang}`] = result.translated.description;
      if (result.translated.requirements) data[`requirements_${otherLang}`] = result.translated.requirements;
      if (result.translated.responsibilities) data[`responsibilities_${otherLang}`] = result.translated.responsibilities;
    }
  } catch {
    // Tarjima xatoligi bo'lsa ham vakansiyani saqlaymiz
  }

  return data;
}

async function handleSubmit() {
  errors.value = {};
  loading.value = true;

  if (!validateForm()) {
    loading.value = false;
    return;
  }

  try {
    let data = getFormData();

    // Avtomatik tarjima
    data = await autoTranslate(data);

    // Saqlash
    loadingPhase.value = 'saving';
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
    loadingPhase.value = '';
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
