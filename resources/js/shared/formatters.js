/**
 * Shared formatters — admin panel va recruiter panel uchun umumiy funksiyalar.
 * Import: import { formatSalary, formatDate, ... } from '@/shared/formatters';
 */

// ─── Location ────────────────────────────────────────────────────────

export function formatLocation(city, district) {
  if (district && city) return `${district}, ${city}`;
  return city || district || '—';
}

export function shortenCity(city) {
  if (!city) return '—';
  return city.replace(' viloyati', '').replace(' shahri', ' sh.');
}

// ─── Salary ──────────────────────────────────────────────────────────

export function formatSalary(min, max, type) {
  if (type === 'negotiable' || (!min && !max)) return 'Kelishiladi';

  const fmt = (num) => new Intl.NumberFormat('uz-UZ').format(num);

  if (min && max) return `${fmt(min)} - ${fmt(max)} so'm`;
  if (min) return `${fmt(min)}+ so'm`;
  return `${fmt(max)} so'm gacha`;
}

// ─── Date ────────────────────────────────────────────────────────────

export function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

export function formatDateTime(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('uz-UZ', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// ─── Vacancy Status ──────────────────────────────────────────────────

export function getStatusLabel(status) {
  const labels = {
    active: 'Faol',
    pending: 'Kutilmoqda',
    paused: 'To\'xtatilgan',
    closed: 'Yopilgan',
    expired: 'Muddati tugagan',
    draft: 'Qoralama',
    rejected: 'Rad etilgan',
  };
  return labels[status] || status;
}

export function getStatusVariant(status) {
  const variants = {
    active: 'success',
    pending: 'warning',
    paused: 'info',
    closed: 'default',
    expired: 'default',
    draft: 'default',
  };
  return variants[status] || 'default';
}

export function getStatusCss(status) {
  const map = {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    closed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    expired: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    draft: 'bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-400',
    paused: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

// ─── Application Status ──────────────────────────────────────────────

export function getApplicationStatusLabel(status) {
  const map = {
    pending: 'Kutilmoqda',
    accepted: 'Qabul qilingan',
    rejected: 'Rad etilgan',
    viewed: 'Ko\'rilgan',
  };
  return map[status] || status;
}

export function getApplicationStatusCss(status) {
  const map = {
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    accepted: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    rejected: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    viewed: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

// ─── Work Type ────────────────────────────────────────────────────────

export function getWorkTypeLabel(type) {
  const labels = {
    full_time: 'To\'liq ish kuni',
    part_time: 'Yarim ish kuni',
    remote: 'Masofaviy',
    freelance: 'Freelance',
  };
  return labels[type] || type || '—';
}
