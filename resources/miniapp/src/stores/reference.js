import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/api'

export const useReferenceStore = defineStore('reference', () => {
  const categories = ref([])
  const cities = ref([])
  const categoriesLoaded = ref(false)
  const citiesLoaded = ref(false)

  async function loadCategories() {
    if (categoriesLoaded.value) return categories.value
    try {
      const res = await api.get('/categories')
      categories.value = res.data.categories || res.data || []
      categoriesLoaded.value = true
    } catch (e) {
      console.error('Failed to load categories:', e)
    }
    return categories.value
  }

  async function loadCities() {
    if (citiesLoaded.value) return cities.value
    try {
      const res = await api.get('/locations')
      cities.value = res.data.cities || []
      citiesLoaded.value = true
    } catch (e) {
      console.error('Failed to load cities:', e)
    }
    return cities.value
  }

  async function loadAll() {
    if (categoriesLoaded.value && citiesLoaded.value) return
    await Promise.all([loadCategories(), loadCities()])
  }

  function getCategoryName(slug, lang = 'uz') {
    if (!slug) return ''
    for (const cat of categories.value) {
      if (cat.slug === slug) {
        return lang === 'ru' ? (cat.name_ru || cat.name_uz) : (cat.name_uz || cat.name_ru)
      }
      if (cat.children) {
        for (const child of cat.children) {
          if (child.slug === slug) {
            return lang === 'ru' ? (child.name_ru || child.name_uz) : (child.name_uz || child.name_ru)
          }
        }
      }
    }
    return slug
  }

  return {
    categories,
    cities,
    categoriesLoaded,
    citiesLoaded,
    loadCategories,
    loadCities,
    loadAll,
    getCategoryName,
  }
})
