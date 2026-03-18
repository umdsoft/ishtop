<template>
  <div class="post-vacancy pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-10 p-4 pb-3" style="background-color: var(--tg-theme-bg-color); border-bottom: 1px solid var(--separator-color);">
      <h1 class="text-lg font-bold" style="color: var(--tg-theme-text-color);">
        {{ t('post.title') }}
      </h1>
      <p class="text-xs mt-0.5" style="color: var(--tg-theme-hint-color);">
        {{ t('post.subtitle') }}
      </p>
    </div>

    <!-- Form -->
    <form class="p-4 space-y-4" @submit.prevent="submitVacancy">
      <!-- Title -->
      <div>
        <label class="label">{{ t('post.vacancy_title') }} *</label>
        <input
          v-model="form.title"
          type="text"
          class="input"
          :placeholder="t('post.title_placeholder')"
          required
        />
      </div>

      <!-- Company Name -->
      <div>
        <label class="label">{{ t('post.company_name') }}</label>
        <input
          v-model="form.company_name"
          type="text"
          class="input"
          :placeholder="t('post.company_placeholder')"
        />
      </div>

      <!-- Category -->
      <div>
        <label class="label">{{ t('post.category') }} *</label>
        <select v-model="form.category" class="input" required>
          <option value="" disabled>{{ t('post.select_category') }}</option>
          <template v-for="cat in referenceStore.categories" :key="cat.slug">
            <option v-if="cat.children && cat.children.length" disabled class="font-bold">
              {{ lang === 'ru' ? cat.name_ru : cat.name_uz }}
            </option>
            <option
              v-if="cat.children && cat.children.length"
              v-for="child in cat.children"
              :key="child.slug"
              :value="child.slug"
            >
              &nbsp;&nbsp;{{ lang === 'ru' ? child.name_ru : child.name_uz }}
            </option>
            <option v-if="!cat.children || !cat.children.length" :value="cat.slug">
              {{ lang === 'ru' ? cat.name_ru : cat.name_uz }}
            </option>
          </template>
        </select>
      </div>

      <!-- Description -->
      <div>
        <label class="label">{{ t('post.description') }} *</label>
        <textarea
          v-model="form.description"
          class="input min-h-[100px]"
          :placeholder="t('post.description_placeholder')"
          required
        />
      </div>

      <!-- Work Type -->
      <div>
        <label class="label">{{ t('post.work_type') }} *</label>
        <div class="grid grid-cols-2 gap-2">
          <button
            v-for="wt in workTypes"
            :key="wt.value"
            type="button"
            class="px-3 py-2.5 rounded-xl text-sm font-medium transition-all active:scale-[0.97]"
            :style="form.work_type === wt.value
              ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
              : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)' }"
            @click="form.work_type = wt.value"
          >
            {{ wt.label }}
          </button>
        </div>
      </div>

      <!-- Region -->
      <div>
        <label class="label">{{ t('post.region') }}</label>
        <select v-model="selectedRegion" class="input">
          <option value="">{{ t('post.select_region') }}</option>
          <option v-for="r in regions" :key="r" :value="r">{{ r }}</option>
        </select>
      </div>

      <!-- City -->
      <div v-if="selectedRegion">
        <label class="label">{{ t('post.city') }}</label>
        <select v-model="form.city" class="input">
          <option value="">{{ t('post.select_city') }}</option>
          <option v-for="city in filteredCities" :key="city.id" :value="cityDisplayName(city)">
            {{ cityDisplayName(city) }}
          </option>
        </select>
      </div>

      <!-- Map Location -->
      <div v-if="form.city">
        <label class="label">{{ t('post.location') }}</label>
        <p class="text-[11px] mb-2" style="color: var(--tg-theme-hint-color);">
          {{ t('post.location_hint') }}
        </p>
        <div ref="mapRef" class="w-full h-[220px] rounded-xl overflow-hidden" style="border: 1px solid var(--separator-color);"></div>
        <div class="flex items-center gap-2 mt-2">
          <button
            type="button"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-medium active:scale-[0.97] transition-all"
            :style="{ backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)' }"
            @click="useMyLocation"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            {{ t('post.my_location') }}
          </button>
          <div v-if="form.latitude" class="text-[11px] flex-1 text-center" style="color: var(--tg-theme-hint-color);">
            {{ form.latitude.toFixed(4) }}, {{ form.longitude.toFixed(4) }}
          </div>
        </div>
      </div>

      <!-- Salary -->
      <div>
        <label class="label">{{ t('post.salary') }}</label>
        <!-- Currency toggle -->
        <div class="flex gap-2 mb-2">
          <button
            v-for="cur in currencies"
            :key="cur.value"
            type="button"
            class="flex-1 py-2 rounded-xl text-sm font-medium transition-all active:scale-[0.97]"
            :style="form.currency === cur.value
              ? { backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }
              : { backgroundColor: 'var(--tg-theme-secondary-bg-color)', color: 'var(--tg-theme-text-color)' }"
            @click="form.currency = cur.value"
          >
            {{ cur.label }}
          </button>
        </div>
        <div class="flex gap-2">
          <input
            :value="salaryMinDisplay"
            @input="onSalaryInput('salary_min', $event)"
            type="text"
            inputmode="numeric"
            class="input flex-1"
            :placeholder="t('search.salary_from')"
          />
          <input
            :value="salaryMaxDisplay"
            @input="onSalaryInput('salary_max', $event)"
            type="text"
            inputmode="numeric"
            class="input flex-1"
            :placeholder="t('search.salary_to')"
          />
        </div>
        <p class="text-[11px] mt-1" style="color: var(--tg-theme-hint-color);">
          {{ t('post.salary_hint') }}
        </p>
      </div>

      <!-- Experience -->
      <div>
        <label class="label">{{ t('post.experience') }}</label>
        <select v-model="form.experience_required" class="input">
          <option value="">{{ t('post.any_experience') }}</option>
          <option v-for="exp in experienceLevels" :key="exp.value" :value="exp.value">
            {{ exp.label }}
          </option>
        </select>
      </div>

      <!-- Contact Phone -->
      <div>
        <label class="label">{{ t('post.contact_phone') }}</label>
        <input
          v-model="form.contact_phone"
          type="tel"
          class="input"
          placeholder="+998 __ ___ __ __"
        />
      </div>

      <!-- Requirements (optional) -->
      <div>
        <label class="label">{{ t('post.requirements') }}</label>
        <textarea
          v-model="form.requirements"
          class="input min-h-[80px]"
          :placeholder="t('post.requirements_placeholder')"
        />
      </div>

      <!-- Auto-translate hint -->
      <p class="text-[11px] text-center" style="color: var(--tg-theme-hint-color);">
        {{ t('post.auto_translate_hint') }}
      </p>

      <!-- Submit -->
      <button
        type="submit"
        class="w-full py-3.5 rounded-xl text-sm font-semibold transition-all active:scale-[0.98]"
        :style="{ backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }"
        :disabled="submitting"
      >
        {{ submitting ? t('post.submitting') : t('post.submit') }}
      </button>
    </form>

    <!-- Success overlay -->
    <div v-if="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center p-6" style="background: rgba(0,0,0,0.5);">
      <div class="rounded-2xl p-6 text-center max-w-sm w-full" style="background-color: var(--tg-theme-bg-color);">
        <div class="text-5xl mb-3">🎉</div>
        <h2 class="text-lg font-bold mb-2" style="color: var(--tg-theme-text-color);">
          {{ t('post.success_title') }}
        </h2>
        <p class="text-sm mb-5" style="color: var(--tg-theme-hint-color);">
          {{ t('post.success_message') }}
        </p>
        <button
          class="w-full py-3 rounded-xl text-sm font-semibold"
          :style="{ backgroundColor: 'var(--tg-theme-button-color)', color: 'var(--tg-theme-button-text-color)' }"
          @click="goToMyVacancies"
        >
          {{ t('my_vacancies.go_to_list') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useLocale } from '@/composables/useLocale'
import { useTelegram } from '@/composables/useTelegram'
import { useVacancyStore } from '@/stores/vacancy'
import { useAuthStore } from '@/stores/auth'
import { useReferenceStore } from '@/stores/reference'
import { formatNumber } from '@/utils/formatters'

const router = useRouter()
const { t, lang } = useLocale()
const telegram = useTelegram()
const vacancyStore = useVacancyStore()
const authStore = useAuthStore()
const referenceStore = useReferenceStore()

let L = null
const selectedRegion = ref('')
const submitting = ref(false)
const showSuccess = ref(false)
const mapRef = ref(null)

let leafletMap = null
let marker = null

const form = ref({
  title: '',
  company_name: '',
  category: '',
  description: '',
  work_type: 'full_time',
  city: '',
  salary_min: null,
  salary_max: null,
  salary_type: 'range',
  currency: 'uzs',
  experience_required: '',
  contact_phone: '',
  requirements: '',
  latitude: null,
  longitude: null,
})

// --- Script detection ---
// Latin → uz, O'zbek kiriллча → uz, Rus kiriллча → ru
function detectLanguage(text) {
  if (!text) return 'uz'
  const latin = (text.match(/[a-zA-Z]/g) || []).length
  const cyrillic = (text.match(/[а-яА-ЯёЁўЎқҚғҒҳҲ]/g) || []).length

  // Lotin harflari ko'p — o'zbekcha
  if (latin > cyrillic) return 'uz'

  // 1) O'zbek kiriллchaga xos harflar (rusda yo'q): Ў, Қ, Ғ, Ҳ
  if (/[ўЎқҚғҒҳҲ]/.test(text)) return 'uz'

  // 2) O'zbek kiriллchada tez-tez ishlatiladigan so'zlar (rusda mavjud emas)
  if (/(керак|учун|билан|ишлаш|тажриба|маош|ходим|лозим|ишчи|ишга|олиш|бериш|ишлаб|ойлик|ёки|мутахассис|талаб|зарур|вазифа|малака|камида|бошқариш|йил|жойи|кераклиги)/.test(text)) return 'uz'

  // Oddiy kiriллча — ruscha
  return cyrillic > 0 ? 'ru' : 'uz'
}

function parseNumber(str) {
  const cleaned = String(str).replace(/\s/g, '').replace(/[^\d]/g, '')
  return cleaned ? parseInt(cleaned, 10) : null
}

const salaryMinDisplay = computed(() => formatNumber(form.value.salary_min))
const salaryMaxDisplay = computed(() => formatNumber(form.value.salary_max))

function onSalaryInput(field, event) {
  const raw = parseNumber(event.target.value)
  form.value[field] = raw
  nextTick(() => {
    event.target.value = formatNumber(raw)
  })
}

const currencies = [
  { value: 'uzs', label: "so'm" },
  { value: 'usd', label: 'USD $' },
]

const workTypes = computed(() => [
  { value: 'full_time', label: t('work_type.full_time') },
  { value: 'part_time', label: t('work_type.part_time') },
  { value: 'remote', label: t('work_type.remote') },
  { value: 'temporary', label: t('work_type.temporary') },
])

const experienceLevels = computed(() => [
  { value: 'no_experience', label: t('experience.no_experience') },
  { value: 'junior', label: t('experience.junior') },
  { value: 'mid', label: t('experience.mid') },
  { value: 'senior', label: t('experience.senior') },
])

const regions = computed(() => {
  const set = new Set(referenceStore.cities.map(c => c.region).filter(Boolean))
  return [...set].sort()
})

const filteredCities = computed(() => {
  if (!selectedRegion.value) return []
  return referenceStore.cities
    .filter(c => c.region === selectedRegion.value)
    .sort((a, b) => {
      if (a.type === b.type) return a.name_uz.localeCompare(b.name_uz)
      return a.type === 'shahar' ? -1 : 1
    })
})

const selectedCityObj = computed(() => {
  if (!form.value.city) return null
  return filteredCities.value.find(c => cityDisplayName(c) === form.value.city) || null
})

function cityDisplayName(city) {
  const name = lang.value === 'ru' ? city.name_ru : city.name_uz
  const type = city.type === 'shahar' ? t('post.type_shahar') : t('post.type_tuman')
  return `${name} ${type}`
}

watch(selectedRegion, () => {
  form.value.city = ''
})

watch(() => form.value.city, async (newCity) => {
  if (!newCity) {
    destroyMap()
    form.value.latitude = null
    form.value.longitude = null
    return
  }

  await nextTick()
  const cityObj = selectedCityObj.value
  if (cityObj && cityObj.latitude && cityObj.longitude) {
    initMap(parseFloat(cityObj.latitude), parseFloat(cityObj.longitude))
  }
})

async function loadLeaflet() {
  if (L) return
  const leaflet = await import('leaflet')
  await import('leaflet/dist/leaflet.css')
  L = leaflet.default || leaflet
}

async function initMap(lat, lng) {
  destroyMap()
  if (!mapRef.value) return

  await loadLeaflet()
  leafletMap = L.map(mapRef.value, {
    center: [lat, lng],
    zoom: 14,
    zoomControl: false,
    attributionControl: false,
  })

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
  }).addTo(leafletMap)

  L.control.zoom({ position: 'bottomright' }).addTo(leafletMap)
  placeMarker(lat, lng)

  leafletMap.on('click', (e) => {
    placeMarker(e.latlng.lat, e.latlng.lng)
  })
}

function placeMarker(lat, lng) {
  if (marker) {
    marker.setLatLng([lat, lng])
  } else {
    marker = L.marker([lat, lng], {
      draggable: true,
      icon: L.divIcon({
        className: 'custom-pin',
        html: '<div style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;"><svg width="28" height="28" viewBox="0 0 24 24" fill="#0D9488"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
      }),
    }).addTo(leafletMap)

    marker.on('dragend', () => {
      const pos = marker.getLatLng()
      form.value.latitude = pos.lat
      form.value.longitude = pos.lng
    })
  }

  form.value.latitude = lat
  form.value.longitude = lng
}

function useMyLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const { latitude, longitude } = pos.coords
        if (leafletMap) {
          leafletMap.setView([latitude, longitude], 16)
          placeMarker(latitude, longitude)
        }
      },
      () => {
        telegram.showAlert(t('search.geo_denied'))
      },
      { enableHighAccuracy: true, timeout: 10000 }
    )
  }
}

function destroyMap() {
  if (marker) { marker = null }
  if (leafletMap) { leafletMap.remove(); leafletMap = null }
}

onBeforeUnmount(() => {
  destroyMap()
})

onMounted(async () => {
  await referenceStore.loadAll()
})

async function ensureAuth() {
  if (authStore.isAuthenticated) return true

  // initData bilan qayta login qilishga urinish
  const tg = window.Telegram?.WebApp
  if (tg?.initData) {
    try {
      await authStore.loginWithTelegram(tg.initData)
      return true
    } catch (e) {
      // login muvaffaqiyatsiz
    }
  }
  return false
}

async function submitVacancy() {
  if (!form.value.title || !form.value.category || !form.value.description || !form.value.work_type) {
    telegram.showAlert(t('post.fill_required'))
    return
  }

  // Autentifikatsiyani tekshirish
  const isAuthed = await ensureAuth()
  if (!isAuthed) {
    telegram.showAlert(t('post.session_expired'))
    return
  }

  submitting.value = true
  try {
    // Detect language from text and put into correct _uz/_ru fields
    const detectedLang = detectLanguage(form.value.title + ' ' + form.value.description)
    const suffix = detectedLang === 'ru' ? 'ru' : 'uz'

    const data = {
      language: detectedLang,
      [`title_${suffix}`]: form.value.title,
      [`description_${suffix}`]: form.value.description,
      category: form.value.category,
      work_type: form.value.work_type,
      currency: form.value.currency,
      salary_type: form.value.salary_type,
    }

    if (form.value.company_name) data.company_name = form.value.company_name
    if (form.value.requirements) data[`requirements_${suffix}`] = form.value.requirements
    if (form.value.salary_min) data.salary_min = form.value.salary_min
    if (form.value.salary_max) data.salary_max = form.value.salary_max
    if (!data.salary_min && !data.salary_max) data.salary_type = 'negotiable'
    if (form.value.experience_required) data.experience_required = form.value.experience_required
    if (form.value.contact_phone) data.contact_phone = form.value.contact_phone
    // city = viloyat nomi, district = tuman/shahar nomi
    if (selectedRegion.value) data.city = selectedRegion.value
    if (form.value.city) data.district = form.value.city
    if (form.value.latitude) {
      data.latitude = form.value.latitude
      data.longitude = form.value.longitude
    }

    // api.js interceptor 401 da avtomatik re-auth + retry qiladi
    await vacancyStore.createVacancy(data)

    telegram.hapticFeedback('success')
    showSuccess.value = true
  } catch (error) {
    const msg = error.response?.data?.message || error.message || t('post.error')
    telegram.showAlert(msg)
    telegram.hapticFeedback('error')
  } finally {
    submitting.value = false
  }
}

function goToMyVacancies() {
  showSuccess.value = false
  router.push('/post')
}
</script>

<style scoped>
.label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
  color: var(--tg-theme-text-color);
}

.input {
  width: 100%;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-text-color);
  border: none;
  outline: none;
}

.input:focus {
  box-shadow: 0 0 0 2px var(--tg-theme-button-color);
}

select.input {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23999' viewBox='0 0 16 16'%3E%3Cpath d='M4.646 5.646a.5.5 0 01.708 0L8 8.293l2.646-2.647a.5.5 0 01.708.708l-3 3a.5.5 0 01-.708 0l-3-3a.5.5 0 010-.708z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
}

textarea.input {
  resize: vertical;
}

:deep(.custom-pin) {
  background: none !important;
  border: none !important;
}

:deep(.leaflet-control-zoom) {
  border: none !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
}

:deep(.leaflet-control-zoom a) {
  background-color: var(--tg-theme-bg-color, #fff) !important;
  color: var(--tg-theme-text-color, #333) !important;
  border-color: var(--separator-color, #ddd) !important;
}
</style>
