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

    <!-- Limit Reached Block -->
    <div
      v-if="limitReached"
      class="max-w-lg mx-auto text-center py-16"
    >
      <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-warning-100 dark:bg-warning-900/20 flex items-center justify-center">
        <ExclamationTriangleIcon class="h-8 w-8 text-warning-500" />
      </div>
      <h2 class="text-xl font-bold text-surface-900 dark:text-surface-100 mb-2">
        Vakansiya limiti tugadi
      </h2>
      <p class="text-surface-600 dark:text-surface-400 mb-6">
        {{ limitMessage }}
      </p>
      <div class="flex items-center justify-center gap-3">
        <AppButton variant="outline" @click="$router.push('/dashboard/vacancies')">
          Orqaga
        </AppButton>
        <router-link to="/dashboard/settings/billing">
          <AppButton variant="primary">
            Obunani yangilash
          </AppButton>
        </router-link>
      </div>
    </div>

    <form v-else @submit.prevent="handleSubmit">
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
                :placeholder="form.language === 'uz' ? 'Senior PHP Developer' : 'Senior PHP Developer'"
                required
                :error="errors.title"
              />

              <div>
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
                  Kategoriya <span class="text-red-500">*</span>
                </label>
                <button
                  type="button"
                  @click="showCategoryModal = true"
                  class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border text-left text-sm transition-colors"
                  :class="errors.category_id
                    ? 'border-red-500 dark:border-red-500'
                    : 'border-surface-300 dark:border-surface-700 hover:border-brand-400 dark:hover:border-brand-500'"
                >
                  <span :class="selectedCategoryLabel ? 'text-surface-900 dark:text-surface-100' : 'text-surface-400 dark:text-surface-500'">
                    {{ selectedCategoryLabel || 'Kategoriya tanlang' }}
                  </span>
                  <ChevronDownIcon class="w-4 h-4 text-surface-400" />
                </button>
                <p v-if="errors.category_id" class="mt-1 text-xs text-red-500">{{ errors.category_id }}</p>
              </div>

              <!-- Category Modal -->
              <AppModal :show="showCategoryModal" title="Kategoriya tanlang" size="lg" @close="showCategoryModal = false">
                <div class="mb-4">
                  <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" />
                    <input
                      v-model="categorySearch"
                      type="text"
                      placeholder="Kategoriya qidirish..."
                      class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-surface-300 dark:border-surface-700 bg-surface-0 dark:bg-surface-800 text-sm text-surface-900 dark:text-surface-100 placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    />
                  </div>
                </div>
                <div class="max-h-[28rem] overflow-y-auto space-y-1">
                  <template v-for="cat in filteredCategories" :key="cat.slug">
                    <!-- Parent category header -->
                    <button
                      type="button"
                      @click="toggleCategory(cat.slug)"
                      class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-left transition-colors"
                      :class="expandedCategory === cat.slug
                        ? 'bg-brand-50 dark:bg-brand-900/20'
                        : 'hover:bg-surface-100 dark:hover:bg-surface-800'"
                    >
                      <ChevronRightIcon
                        class="w-4 h-4 text-surface-400 transition-transform duration-200 flex-shrink-0"
                        :class="{ 'rotate-90': expandedCategory === cat.slug || categorySearch }"
                      />
                      <span class="text-sm font-semibold" :class="expandedCategory === cat.slug ? 'text-brand-600 dark:text-brand-400' : 'text-surface-800 dark:text-surface-200'">
                        {{ cat.name_uz }}
                      </span>
                      <span class="ml-auto text-xs text-surface-400">{{ cat.filteredChildren.length }}</span>
                    </button>
                    <!-- Children (sub-categories) -->
                    <div v-if="expandedCategory === cat.slug || categorySearch" class="ml-4 pl-3 border-l-2 border-surface-200 dark:border-surface-700 space-y-0.5 pb-1">
                      <button
                        v-for="child in cat.filteredChildren"
                        :key="child.slug"
                        type="button"
                        @click="selectCategory(cat, child)"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg transition-colors flex items-center gap-2"
                        :class="form.category_id && (typeof form.category_id === 'object' ? form.category_id.slug : form.category_id) === child.slug
                          ? 'bg-brand-500 text-white font-medium'
                          : 'text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800'"
                      >
                        {{ child.name_uz }}
                      </button>
                    </div>
                  </template>
                  <div v-if="filteredCategories.length === 0" class="px-4 py-8 text-center text-sm text-surface-400">
                    Natija topilmadi
                  </div>
                </div>
              </AppModal>

              <div>
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
                  Lokatsiya <span class="text-red-500">*</span>
                </label>
                <button
                  type="button"
                  @click="showLocationModal = true"
                  class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border text-left text-sm transition-colors"
                  :class="errors.region_id
                    ? 'border-red-500 dark:border-red-500'
                    : 'border-surface-300 dark:border-surface-700 hover:border-brand-400 dark:hover:border-brand-500'"
                >
                  <span :class="selectedLocationLabel ? 'text-surface-900 dark:text-surface-100' : 'text-surface-400 dark:text-surface-500'">
                    {{ selectedLocationLabel || 'Viloyat va tumanni tanlang' }}
                  </span>
                  <ChevronDownIcon class="w-4 h-4 text-surface-400" />
                </button>
                <p v-if="errors.region_id" class="mt-1 text-xs text-red-500">{{ errors.region_id }}</p>
              </div>

              <!-- Location Modal -->
              <AppModal :show="showLocationModal" title="Lokatsiya tanlang" size="lg" @close="showLocationModal = false">
                <div class="mb-4">
                  <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" />
                    <input
                      v-model="locationSearch"
                      type="text"
                      placeholder="Viloyat yoki tuman qidirish..."
                      class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-surface-300 dark:border-surface-700 bg-surface-0 dark:bg-surface-800 text-sm text-surface-900 dark:text-surface-100 placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    />
                  </div>
                </div>
                <div class="max-h-[28rem] overflow-y-auto space-y-1">
                  <template v-for="region in filteredLocations" :key="region.key">
                    <!-- Region header -->
                    <button
                      type="button"
                      @click="toggleLocation(region.key)"
                      class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-left transition-colors"
                      :class="expandedLocation === region.key
                        ? 'bg-brand-50 dark:bg-brand-900/20'
                        : 'hover:bg-surface-100 dark:hover:bg-surface-800'"
                    >
                      <ChevronRightIcon
                        class="w-4 h-4 text-surface-400 transition-transform duration-200 flex-shrink-0"
                        :class="{ 'rotate-90': expandedLocation === region.key || locationSearch }"
                      />
                      <span class="text-sm font-semibold" :class="expandedLocation === region.key ? 'text-brand-600 dark:text-brand-400' : 'text-surface-800 dark:text-surface-200'">
                        {{ region.name }}
                      </span>
                      <span class="ml-auto text-xs text-surface-400">{{ region.filteredDistricts.length }}</span>
                    </button>
                    <!-- Districts -->
                    <div v-if="expandedLocation === region.key || locationSearch" class="ml-4 pl-3 border-l-2 border-surface-200 dark:border-surface-700 space-y-0.5 pb-1">
                      <button
                        v-for="dist in region.filteredDistricts"
                        :key="dist.id"
                        type="button"
                        @click="selectLocation(region, dist)"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg transition-colors flex items-center gap-2"
                        :class="form.district_id && (typeof form.district_id === 'object' ? form.district_id.id : form.district_id) === dist.id
                          ? 'bg-brand-500 text-white font-medium'
                          : 'text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800'"
                      >
                        {{ dist.name }}
                      </button>
                    </div>
                  </template>
                  <div v-if="filteredLocations.length === 0" class="px-4 py-8 text-center text-sm text-surface-400">
                    Natija topilmadi
                  </div>
                </div>
              </AppModal>

              <!-- Map -->
              <div v-if="form.district_id">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">
                  Xaritadan belgilang
                </label>
                <p class="text-xs text-surface-400 dark:text-surface-500 mb-2">
                  Xaritada bosib aniq manzilni belgilang yoki markerni suring
                </p>
                <div ref="mapRef" class="w-full h-[240px] rounded-xl overflow-hidden border border-surface-300 dark:border-surface-700"></div>
                <div class="flex items-center gap-2 mt-2">
                  <button
                    type="button"
                    @click="useMyLocation"
                    class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300 hover:bg-surface-200 dark:hover:bg-surface-700"
                  >
                    <MapPinIcon class="w-4 h-4" />
                    Mening joylashuvim
                  </button>
                  <span v-if="form.latitude" class="text-xs text-surface-400 ml-auto">
                    {{ form.latitude.toFixed(4) }}, {{ form.longitude.toFixed(4) }}
                  </span>
                </div>
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
                      :model-value="formatSalary(form.salary_min)"
                      @update:model-value="v => form.salary_min = parseSalary(v)"
                      type="text"
                      inputmode="numeric"
                      label="Minimal (so'm)"
                      placeholder="3 000 000"
                      :error="errors.salary_min"
                    />

                    <AppInput
                      :model-value="formatSalary(form.salary_max)"
                      @update:model-value="v => form.salary_max = parseSalary(v)"
                      type="text"
                      inputmode="numeric"
                      label="Maksimal (so'm)"
                      placeholder="5 000 000"
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
                  Vakansiya yaratish
                </template>
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
                  Xohlagan tilda yozing — ikkinchi tilga AI avtomatik tarjima qiladi. Saqlangandan so'ng savolnoma qo'shishingiz mumkin.
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
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { ArrowLeftIcon, InformationCircleIcon, LanguageIcon, SparklesIcon, ExclamationTriangleIcon, ChevronDownIcon, ChevronRightIcon, MagnifyingGlassIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import AppModal from '../../components/ui/AppModal.vue';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppTextarea from '../../components/ui/AppTextarea.vue';
import AppSelect from '../../components/ui/AppSelect.vue';
import AppCheckbox from '../../components/ui/AppCheckbox.vue';
import AppDatePicker from '../../components/ui/AppDatePicker.vue';
import { useLocations } from '../../composables/useLocations.js';
import { useAuthStore } from '../../stores/auth';
const { regions, cities: allCities, load: loadLocations, getDistrictsForRegion } = useLocations();
const authStore = useAuthStore();

const router = useRouter();

const limitReached = ref(false);
const limitMessage = ref('');
const canTranslate = ref(false);

onMounted(async () => {
  loadLocations();
  try {
    const { data } = await axios.get('/api/subscriptions/current');
    canTranslate.value = !!data.limits?.ai_translation;
    if (!data.can_create_vacancy) {
      limitReached.value = true;
      const max = data.limits?.max_vacancies;
      limitMessage.value = `${data.plan_label} rejada maksimum ${max} ta faol vakansiya yaratish mumkin. Obunangizni yangilang yoki mavjud vakansiyalarni yoping.`;
    }
  } catch {
    // Continue even if check fails
  }

  // Load categories from API
  try {
    const { data } = await axios.get('/api/categories');
    rawCategories.value = data.categories || [];
  } catch {
    // Fallback if API fails
  }

  // Pre-fill contact info from user profile
  const user = authStore.user;
  if (user) {
    form.value.contact_name = [user.first_name, user.last_name].filter(Boolean).join(' ');
    form.value.contact_phone = user.phone || '';
  }
});

const form = ref({
  language: 'uz',
  title: '',
  parent_category: null,
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
  latitude: null,
  longitude: null,
});

const mapRef = ref(null);
const errors = ref({});
const loading = ref(false);
const loadingPhase = ref(''); // 'translating' | 'saving' | ''

const rawCategories = ref([]);
const showCategoryModal = ref(false);
const categorySearch = ref('');
const expandedCategory = ref(null);

function toggleCategory(slug) {
  expandedCategory.value = expandedCategory.value === slug ? null : slug;
}

const filteredCategories = computed(() => {
  const q = categorySearch.value.toLowerCase().trim();
  return rawCategories.value
    .map(cat => {
      const children = (cat.children || []).filter(ch =>
        !q || ch.name_uz.toLowerCase().includes(q) || ch.name_ru?.toLowerCase().includes(q)
      );
      return children.length ? { ...cat, filteredChildren: children } : null;
    })
    .filter(Boolean);
});

const selectedCategoryLabel = computed(() => {
  const catId = form.value.category_id;
  const slug = catId && typeof catId === 'object' ? catId.slug : catId;
  if (!slug) return '';
  for (const cat of rawCategories.value) {
    const child = (cat.children || []).find(ch => ch.slug === slug);
    if (child) return `${cat.name_uz} → ${child.name_uz}`;
  }
  return slug;
});

function selectCategory(parent, child) {
  form.value.parent_category = parent.slug;
  form.value.category_id = { slug: child.slug, name: child.name_uz };
  showCategoryModal.value = false;
  categorySearch.value = '';
}

const employmentTypes = [
  { value: 'full_time', label: 'To\'liq ish kuni' },
  { value: 'part_time', label: 'Yarim ish kuni' },
  { value: 'remote', label: 'Masofaviy' },
  { value: 'temporary', label: 'Vaqtinchalik' },
];

const experienceLevels = [
  { value: 'no_experience', label: 'Tajribasiz' },
  { value: 'junior', label: 'Junior (1-3 yil)' },
  { value: 'mid', label: 'Middle (3-5 yil)' },
  { value: 'senior', label: 'Senior (5+ yil)' },
];

const statusOptions = [
  { value: 'draft', label: 'Qoralama' },
  { value: 'pending', label: 'Kutilmoqda' },
  { value: 'active', label: 'Faol' },
];

// Set initial option objects so AppSelect displays labels correctly
form.value.employment_type = employmentTypes[0];
form.value.experience_level = experienceLevels[2];
form.value.status = statusOptions[0];

const showLocationModal = ref(false);
const locationSearch = ref('');
const expandedLocation = ref(null);

function toggleLocation(key) {
  expandedLocation.value = expandedLocation.value === key ? null : key;
}

const filteredLocations = computed(() => {
  const q = locationSearch.value.toLowerCase().trim();
  return regions.value
    .map(region => {
      const districts = getDistrictsForRegion(region.key).filter(d =>
        !q || d.name.toLowerCase().includes(q) || d.name_uz?.toLowerCase().includes(q) || d.name_ru?.toLowerCase().includes(q) || region.name.toLowerCase().includes(q)
      );
      return districts.length ? { ...region, filteredDistricts: districts } : null;
    })
    .filter(Boolean);
});

const selectedLocationLabel = computed(() => {
  const region = form.value.region_id;
  const dist = form.value.district_id;
  const regionName = region && typeof region === 'object' ? region.name : '';
  const distName = dist && typeof dist === 'object' ? dist.name : '';
  if (regionName && distName) return `${regionName} → ${distName}`;
  if (regionName) return regionName;
  return '';
});

function selectLocation(region, dist) {
  form.value.region_id = regions.value.find(r => r.key === region.key) || null;
  form.value.district_id = dist;
  showLocationModal.value = false;
  locationSearch.value = '';
  // Init map at district coordinates
  if (dist.latitude && dist.longitude) {
    nextTick(() => initMap(dist.latitude, dist.longitude));
  }
}

// --- Leaflet Map ---
let leafletMap = null;
let leafletMarker = null;
let L = null;

async function loadLeaflet() {
  if (L) return;
  const leaflet = await import('leaflet');
  await import('leaflet/dist/leaflet.css');
  L = leaflet.default || leaflet;
}

async function initMap(lat, lng) {
  destroyMap();
  if (!mapRef.value) return;

  await loadLeaflet();
  leafletMap = L.map(mapRef.value, {
    center: [lat, lng],
    zoom: 14,
    zoomControl: false,
    attributionControl: false,
  });

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
  }).addTo(leafletMap);

  L.control.zoom({ position: 'bottomright' }).addTo(leafletMap);
  placeMarker(lat, lng);

  leafletMap.on('click', (e) => {
    placeMarker(e.latlng.lat, e.latlng.lng);
  });
}

function placeMarker(lat, lng) {
  if (leafletMarker) {
    leafletMarker.setLatLng([lat, lng]);
  } else {
    leafletMarker = L.marker([lat, lng], {
      draggable: true,
      icon: L.divIcon({
        className: 'custom-pin',
        html: '<div style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;"><svg width="28" height="28" viewBox="0 0 24 24" fill="#0D9488"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
      }),
    }).addTo(leafletMap);

    leafletMarker.on('dragend', () => {
      const pos = leafletMarker.getLatLng();
      form.value.latitude = pos.lat;
      form.value.longitude = pos.lng;
    });
  }

  form.value.latitude = lat;
  form.value.longitude = lng;
}

function useMyLocation() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const { latitude, longitude } = pos.coords;
      if (leafletMap) {
        leafletMap.setView([latitude, longitude], 16);
        placeMarker(latitude, longitude);
      }
    },
    () => { /* silently fail */ },
    { enableHighAccuracy: true, timeout: 10000 }
  );
}

function destroyMap() {
  if (leafletMarker) { leafletMarker = null; }
  if (leafletMap) { leafletMap.remove(); leafletMap = null; }
}

onBeforeUnmount(() => {
  destroyMap();
});

const minDeadline = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

function formatSalary(val) {
  if (val === null || val === undefined || val === '') return '';
  return String(val).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function parseSalary(val) {
  const num = parseInt(String(val).replace(/\s/g, ''), 10);
  if (isNaN(num)) return null;
  return Math.min(num, 2000000000);
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

  // Map category_id to category slug
  if (data.category_id) {
    data.category = typeof data.category_id === 'object'
      ? data.category_id.slug
      : data.category_id;
  }
  delete data.category_id;
  delete data.parent_category;

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

  // Map region_id to city (backend expects region key string)
  if (data.region_id) {
    const region = typeof data.region_id === 'object' ? data.region_id : null;
    data.city = region?.key || region?.name || '';
  }
  delete data.region_id;

  // Map district_id to district (backend expects city name string)
  if (data.district_id) {
    const dist = typeof data.district_id === 'object' ? data.district_id : null;
    data.district = dist?.name_uz || dist?.name || '';
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

  // Map is_featured to is_top (backend field)
  data.is_top = !!data.is_featured;
  delete data.is_featured;

  // Map require_questionnaire to has_questionnaire (backend field)
  data.has_questionnaire = !!data.require_questionnaire;
  delete data.require_questionnaire;

  // Map deadline to expires_at (backend field)
  if (data.deadline) {
    data.expires_at = data.deadline;
  }
  delete data.deadline;

  // Remove fields not in backend schema
  delete data.max_applicants;
  delete data.auto_reject_unqualified;
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
    const response = await axios.post('/api/recruiter/vacancies', data);
    const newId = response.data.vacancy?.id;

    toast.success('Vakansiya muvaffaqiyatli yaratildi!');
    if (newId) {
      router.push({ name: 'vacancy-detail', params: { id: newId }, query: { tab: 'recommended' } });
    } else {
      router.push('/dashboard/vacancies');
    }
  } catch (error) {
    if (error.response?.data?.limit_reached) {
      limitReached.value = true;
      limitMessage.value = error.response.data.message;
      return;
    }
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi. Qaytadan urinib ko\'ring.');
  } finally {
    loading.value = false;
    loadingPhase.value = '';
  }
}

async function saveDraft() {
  loading.value = true;
  form.value.status = statusOptions[0]; // draft

  try {
    let data = getFormData();
    data.status = 'draft';

    // Avtomatik tarjima
    data = await autoTranslate(data);

    loadingPhase.value = 'saving';
    await axios.post('/api/recruiter/vacancies', data);

    toast.success('Qoralama saqlandi');
    router.push('/dashboard/vacancies');
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
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

  if (!form.value.contact_phone) {
    newErrors.contact_phone = 'Telefon raqami kiritilishi shart';
  }

  if (!form.value.salary_negotiable) {
    const minSalary = form.value.salary_min;
    const maxSalary = form.value.salary_max;
    if (minSalary && minSalary > 2000000000) {
      newErrors.salary_min = 'Maosh 2 000 000 000 dan oshmasligi kerak';
    }
    if (maxSalary && maxSalary > 2000000000) {
      newErrors.salary_max = 'Maosh 2 000 000 000 dan oshmasligi kerak';
    }
    if (minSalary && maxSalary && minSalary > maxSalary) {
      newErrors.salary_min = 'Minimal maosh maksimaldan katta bo\'lmasligi kerak';
    }
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
}
</script>
