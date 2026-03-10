/**
 * Centralized utility functions — DRY
 * Previously duplicated across 5+ views
 */

/**
 * Format salary range for display
 * @param {Object} vacancy - vacancy object with salary_min, salary_max
 * @param {Function} t - i18n translation function
 * @param {Object} options
 * @param {boolean} options.short - if true, returns compact format (for cards/lists)
 * @returns {string}
 */
export function formatSalary(vacancy, t, { short = false } = {}) {
  if (!vacancy) return ''
  const fmt = (n) => new Intl.NumberFormat('uz-UZ').format(n)

  if (vacancy.salary_min && vacancy.salary_max) {
    if (vacancy.salary_min === vacancy.salary_max) {
      return short ? fmt(vacancy.salary_min) : `${fmt(vacancy.salary_min)} ${t('common.som')}`
    }
    return short
      ? `${fmt(vacancy.salary_min)} - ${fmt(vacancy.salary_max)}`
      : `${fmt(vacancy.salary_min)} - ${fmt(vacancy.salary_max)} ${t('common.som')}`
  } else if (vacancy.salary_min) {
    return short
      ? `${fmt(vacancy.salary_min)}+`
      : `${fmt(vacancy.salary_min)} ${t('common.som_from')}`
  } else if (vacancy.salary_max) {
    return short
      ? `${fmt(vacancy.salary_max)} ${t('common.som_to')}`
      : `${fmt(vacancy.salary_max)} ${t('common.som_to')}`
  }
  return short ? '' : t('common.negotiable')
}

/**
 * Format relative time ago
 * @param {string} date - ISO date string
 * @param {Function} t - i18n translation function
 * @returns {string}
 */
export function timeAgo(date, t) {
  if (!date) return ''
  const seconds = Math.floor((Date.now() - new Date(date).getTime()) / 1000)

  if (seconds < 60) return t('time.just_now')
  if (seconds < 3600) return `${Math.floor(seconds / 60)} ${t('time.minutes_ago')}`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} ${t('time.hours_ago')}`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} ${t('time.days_ago')}`

  return new Date(date).toLocaleDateString('uz-UZ')
}

/**
 * Get first letter of name, uppercased
 * @param {string} name
 * @returns {string}
 */
export function getInitial(name) {
  return name ? name.charAt(0).toUpperCase() : '?'
}

/**
 * Format number with locale separators
 * @param {number} n
 * @returns {string}
 */
export function formatNumber(n) {
  if (n === null || n === undefined) return ''
  return new Intl.NumberFormat('uz-UZ').format(n)
}

/**
 * Format date for display
 * @param {string} date - ISO date string
 * @param {Object} options
 * @param {boolean} options.short - if true, returns "12 yan" format
 * @returns {string}
 */
export function formatDate(date, { short = false } = {}) {
  if (!date) return ''
  const d = new Date(date)
  if (isNaN(d.getTime())) return ''

  if (short) {
    return d.toLocaleDateString('uz-UZ', { day: 'numeric', month: 'short' })
  }
  return d.toLocaleDateString('uz-UZ', { year: 'numeric', month: 'long', day: 'numeric' })
}

/**
 * Calculate distance between two coordinates (Haversine formula)
 * @returns {number|null} distance in km, or null if coords missing
 */
export function calculateDistance(lat1, lng1, lat2, lng2) {
  if (!lat1 || !lng1 || !lat2 || !lng2) return null
  const R = 6371
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLng = (lng2 - lng1) * Math.PI / 180
  const a = Math.sin(dLat / 2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLng / 2) ** 2
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
}

/**
 * Simplified match percentage (client-side heuristic)
 * @param {Object} vacancy - vacancy with category, city, salary_min/max, work_type
 * @param {Object} wp - worker profile { city, preferred_categories, expected_salary_min/max, work_types }
 * @returns {number|null} 0-100 or null
 */
export function calculateMatchPercent(vacancy, wp) {
  if (!vacancy || !wp) return null
  let score = 0, max = 0

  if (wp.preferred_categories?.length) {
    max += 35
    if (wp.preferred_categories.includes(vacancy.category)) score += 35
  }
  if (wp.city) {
    max += 25
    if (vacancy.city === wp.city) score += 25
  }
  if (wp.expected_salary_min || wp.expected_salary_max) {
    max += 25
    const wMin = wp.expected_salary_min || 0
    const wMax = wp.expected_salary_max || Infinity
    const vMin = vacancy.salary_min || 0
    const vMax = vacancy.salary_max || Infinity
    if (vMax >= wMin && vMin <= wMax) score += 25
  }
  if (wp.work_types?.length && vacancy.work_type) {
    max += 15
    if (wp.work_types.includes(vacancy.work_type)) score += 15
  }

  return max === 0 ? null : Math.round((score / max) * 100)
}
