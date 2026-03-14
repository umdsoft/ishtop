import { ref } from 'vue';
import axios from 'axios';

const regions = ref([]);
const cities = ref([]);
const loaded = ref(false);
const loading = ref(false);

/**
 * Yagona location composable — /api/locations dan oladi.
 * Bir marta yuklaydi, keyin cache qiladi (SPA ichida).
 */
export function useLocations() {
  async function load() {
    if (loaded.value || loading.value) return;
    loading.value = true;
    try {
      const { data } = await axios.get('/api/locations');
      regions.value = data.regions.map((r, i) => ({
        id: i + 1,
        key: r.key,
        name: r.name_uz,
        name_uz: r.name_uz,
        name_ru: r.name_ru,
      }));
      cities.value = data.cities.map(c => ({
        id: c.id,
        region_key: c.region,
        name: c.type ? `${c.name_uz} (${c.type})` : c.name_uz,
        name_uz: c.name_uz,
        name_ru: c.name_ru,
        type: c.type,
      }));
      loaded.value = true;
    } catch (e) {
      console.error('Failed to load locations', e);
    } finally {
      loading.value = false;
    }
  }

  function getDistrictsForRegion(regionKey) {
    if (!regionKey) return [];
    return cities.value.filter(c => c.region_key === regionKey);
  }

  function findRegionByKey(key) {
    return regions.value.find(r => r.key === key) || null;
  }

  function findCityByName(name) {
    return cities.value.find(c => c.name_uz === name || c.name_ru === name) || null;
  }

  return {
    regions,
    cities,
    loaded,
    loading,
    load,
    getDistrictsForRegion,
    findRegionByKey,
    findCityByName,
  };
}
