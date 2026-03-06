import { ref, computed } from 'vue'
import uz from '@/locales/uz'
import ru from '@/locales/ru'

const STORAGE_KEY = 'ishtop_lang'

// Reactive language ref — shared across all components
const currentLang = ref(localStorage.getItem(STORAGE_KEY) || 'uz')

export function useLocale() {
  const lang = computed(() => currentLang.value)
  const messages = computed(() => currentLang.value === 'ru' ? ru : uz)

  function t(key) {
    const keys = key.split('.')
    let value = messages.value
    for (const k of keys) {
      value = value?.[k]
    }
    return value || key
  }

  function setLang(newLang) {
    currentLang.value = newLang
    localStorage.setItem(STORAGE_KEY, newLang)
  }

  return { t, lang, setLang }
}
