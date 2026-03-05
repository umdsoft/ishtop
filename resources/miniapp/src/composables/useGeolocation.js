import { ref } from 'vue'

export function useGeolocation() {
  const coords = ref(null)
  const error = ref(null)
  const loading = ref(false)

  const getLocation = () => {
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
          loading.value = false
          resolve(coords.value)
        },
        (err) => {
          error.value = err.message
          loading.value = false
          reject(err)
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
