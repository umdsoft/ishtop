import { ref } from 'vue'

const GEO_CACHE_KEY = 'kadrgo_geo'
const GEO_CACHE_TTL = 5 * 60 * 1000 // 5 minutes

function getCachedLocation() {
  try {
    const raw = sessionStorage.getItem(GEO_CACHE_KEY)
    if (!raw) return null
    const cached = JSON.parse(raw)
    if (Date.now() - cached.ts < GEO_CACHE_TTL) return { lat: cached.lat, lng: cached.lng }
  } catch {}
  return null
}

function cacheLocation(lat, lng) {
  try {
    sessionStorage.setItem(GEO_CACHE_KEY, JSON.stringify({ lat, lng, ts: Date.now() }))
  } catch {}
}

export function useGeolocation() {
  const coords = ref(null)
  const error = ref(null)
  const loading = ref(false)

  const getLocation = ({ timeout = 5000 } = {}) => {
    // Return cached location instantly if available
    const cached = getCachedLocation()
    if (cached) {
      coords.value = cached
      return Promise.resolve(cached)
    }

    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        error.value = 'Geolokatsiya qo\'llab-quvvatlanmaydi'
        reject(error.value)
        return
      }

      loading.value = true

      navigator.geolocation.getCurrentPosition(
        (position) => {
          coords.value = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          }
          cacheLocation(coords.value.lat, coords.value.lng)
          loading.value = false
          resolve(coords.value)
        },
        (err) => {
          error.value = err.message
          loading.value = false
          reject(err)
        },
        {
          enableHighAccuracy: false,
          timeout,
          maximumAge: 300000, // Accept 5-minute old position from browser cache
        }
      )
    })
  }

  return {
    coords,
    error,
    loading,
    getLocation,
  }
}
