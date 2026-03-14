<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Viloyatlar va shaharlar</h1>

    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="h-14 bg-surface-200 dark:bg-surface-700 rounded-xl animate-pulse" />
    </div>

    <!-- Region Accordion -->
    <div v-else class="space-y-2">
      <div
        v-for="region in regions"
        :key="region.key"
        class="rounded-xl border border-surface-200 dark:border-surface-700/60 bg-white dark:bg-surface-800/80 overflow-hidden"
      >
        <!-- Region Header -->
        <button
          @click="toggle(region.key)"
          class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors"
        >
          <div class="flex items-center gap-3">
            <MapPinIcon class="w-5 h-5 text-brand-500 shrink-0" />
            <div>
              <span class="font-semibold text-surface-900 dark:text-surface-100">{{ region.name_uz }}</span>
              <span class="ml-2 text-xs text-surface-500 dark:text-surface-400">{{ region.name_ru }}</span>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400">
              {{ getCities(region.key).length }} ta
            </span>
            <ChevronDownIcon
              class="w-5 h-5 text-surface-400 transition-transform duration-200"
              :class="{ 'rotate-180': openRegion === region.key }"
            />
          </div>
        </button>

        <!-- Cities List -->
        <div v-if="openRegion === region.key" class="border-t border-surface-200 dark:border-surface-700/60">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-surface-50 dark:bg-surface-800/50">
                  <th class="text-left py-2.5 px-5 font-medium text-surface-500 dark:text-surface-400 w-8">#</th>
                  <th class="text-left py-2.5 px-5 font-medium text-surface-500 dark:text-surface-400">Nomi (UZ)</th>
                  <th class="text-left py-2.5 px-5 font-medium text-surface-500 dark:text-surface-400">Nomi (RU)</th>
                  <th class="text-left py-2.5 px-5 font-medium text-surface-500 dark:text-surface-400">Turi</th>
                  <th class="text-right py-2.5 px-5 font-medium text-surface-500 dark:text-surface-400">Amal</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(city, idx) in getCities(region.key)"
                  :key="city.id"
                  class="border-t border-surface-100 dark:border-surface-800/40 hover:bg-surface-50/50 dark:hover:bg-surface-800/20"
                >
                  <td class="py-2.5 px-5 text-surface-400 text-xs">{{ idx + 1 }}</td>
                  <td class="py-2.5 px-5 font-medium text-surface-900 dark:text-surface-100">{{ city.name_uz }}</td>
                  <td class="py-2.5 px-5 text-surface-600 dark:text-surface-400">{{ city.name_ru }}</td>
                  <td class="py-2.5 px-5">
                    <span
                      :class="[
                        'text-xs px-2 py-0.5 rounded-full font-medium',
                        city.type === 'shahar'
                          ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400'
                          : 'bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-400'
                      ]"
                    >
                      {{ city.type || '—' }}
                    </span>
                  </td>
                  <td class="py-2.5 px-5 text-right">
                    <button
                      @click="openEditModal(city)"
                      class="text-xs px-2.5 py-1.5 rounded-lg font-medium bg-brand-50 text-brand-700 hover:bg-brand-100 dark:bg-brand-950/30 dark:text-brand-400 transition-colors"
                    >
                      <PencilIcon class="w-3.5 h-3.5 inline" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
      <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-md mx-4 border border-surface-200 dark:border-surface-800">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Shaharni tahrirlash</h3>
        </div>
        <div class="px-6 py-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Nomi (UZ)</label>
            <input v-model="form.name_uz" type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Nomi (RU)</label>
            <input v-model="form.name_ru" type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Turi</label>
            <select v-model="form.type"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
              <option value="shahar">Shahar</option>
              <option value="tuman">Tuman</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800 flex justify-end gap-3">
          <button @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
            Bekor qilish
          </button>
          <button @click="saveCity" :disabled="saving"
            class="px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors disabled:opacity-50">
            {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { MapPinIcon, ChevronDownIcon, PencilIcon } from '@heroicons/vue/24/outline';

const loading = ref(true);
const regions = ref([]);
const cities = ref([]);
const openRegion = ref(null);

const showModal = ref(false);
const editingCity = ref(null);
const saving = ref(false);
const form = ref({ name_uz: '', name_ru: '', type: 'tuman' });

function getCities(regionKey) {
  return cities.value.filter(c => c.region === regionKey);
}

function toggle(regionKey) {
  openRegion.value = openRegion.value === regionKey ? null : regionKey;
}

function openEditModal(city) {
  editingCity.value = city;
  form.value = {
    name_uz: city.name_uz,
    name_ru: city.name_ru || '',
    type: city.type || 'tuman',
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingCity.value = null;
}

async function saveCity() {
  saving.value = true;
  try {
    await axios.put(`/api/admin/cities/${editingCity.value.id}`, form.value);
    Object.assign(editingCity.value, form.value);
    toast.success('Shahar yangilandi');
    closeModal();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  } finally {
    saving.value = false;
  }
}

async function fetchLocations() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/locations');
    regions.value = data.regions;
    cities.value = data.cities;
  } catch (err) {
    toast.error('Ma\'lumotlar yuklanmadi');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchLocations);
</script>
