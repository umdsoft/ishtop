<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-3 mb-2">
        <button
          class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
          @click="$router.push('/dashboard/vacancies')"
        >
          <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
        </button>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Yangi vakansiya</h1>
      </div>
      <p class="text-surface-600 dark:text-surface-400 ml-14">Vakansiya e'lon qiling va arizalar qabul qilishni boshlang</p>
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Information -->
          <AppCard>
            <template #header>
              <h2 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Asosiy ma'lumotlar</h2>
            </template>

            <div class="space-y-4">
              <AppInput
                v-model="form.title"
                label="Vakansiya nomi"
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
                label="Tavsif"
                placeholder="Vakansiya haqida batafsil ma'lumot..."
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
                label="Talablar"
                placeholder="- Kamida 3 yillik tajriba&#10;- PHP va Laravel bilishi&#10;- MySQL bilishi"
                :rows="5"
                hint="Har bir talabni yangi qatordan boshlang"
                :error="errors.requirements"
              />

              <AppTextarea
                v-model="form.responsibilities"
                label="Mas'uliyatlar"
                placeholder="- Backend dasturlash&#10;- API yaratish&#10;- Database optimallashtirish"
                :rows="5"
                hint="Har bir mas'uliyatni yangi qatordan boshlang"
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
                Vakansiya yaratish
              </AppButton>

              <AppButton
                type="button"
                variant="outline"
                size="lg"
                full-width
                :disabled="loading"
                @click="saveDraft"
              >
                Qoralama saqlash
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

          <!-- Help -->
          <AppCard>
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-lg bg-info-100 dark:bg-info-900/20 flex items-center justify-center">
                  <InformationCircleIcon class="h-6 w-6 text-info-600 dark:text-info-400" />
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-surface-900 dark:text-surface-100 mb-1">Ko'rsatma</h4>
                <p class="text-sm text-surface-600 dark:text-surface-400">
                  Vakansiya yaratilgandan so'ng savolnoma qo'shishingiz va Telegram kanalda e'lon qilishingiz mumkin.
                </p>
              </div>
            </div>
          </AppCard>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue-sonner';
import { ArrowLeftIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppTextarea from '../../components/ui/AppTextarea.vue';
import AppSelect from '../../components/ui/AppSelect.vue';
import AppCheckbox from '../../components/ui/AppCheckbox.vue';
import AppDatePicker from '../../components/ui/AppDatePicker.vue';

const router = useRouter();

const form = ref({
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

const regions = ref([
  { id: 1, name: 'Toshkent shahri' },
  { id: 2, name: 'Toshkent viloyati' },
  { id: 3, name: 'Samarqand' },
  { id: 4, name: 'Buxoro' },
  { id: 5, name: 'Farg\'ona' },
]);

const districts = ref([
  { id: 1, region_id: 1, name: 'Yunusobod' },
  { id: 2, region_id: 1, name: 'Chilonzor' },
  { id: 3, region_id: 1, name: 'Yashnobod' },
  { id: 4, region_id: 2, name: 'Ohangaron' },
  { id: 5, region_id: 2, name: 'Chirchiq' },
]);

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
];

const filteredDistricts = computed(() => {
  if (!form.value.region_id) return [];
  return districts.value.filter(d => d.region_id === form.value.region_id);
});

const minDeadline = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

async function handleSubmit() {
  errors.value = {};
  loading.value = true;

  // Validate
  if (!validateForm()) {
    loading.value = false;
    return;
  }

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));

    toast.success('Vakansiya muvaffaqiyatli yaratildi!');
    router.push('/dashboard/vacancies');
  } catch (error) {
    toast.error('Xatolik yuz berdi. Qaytadan urinib ko\'ring.');
  } finally {
    loading.value = false;
  }
}

async function saveDraft() {
  loading.value = true;
  form.value.status = 'draft';

  try {
    await new Promise(resolve => setTimeout(resolve, 1000));
    toast.success('Qoralama saqlandi');
    router.push('/dashboard/vacancies');
  } catch (error) {
    toast.error('Xatolik yuz berdi');
  } finally {
    loading.value = false;
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
</script>
