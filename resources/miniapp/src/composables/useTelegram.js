import { ref, computed } from 'vue'

const tg = window.Telegram?.WebApp

export function useTelegram() {
  const webApp = ref(tg)

  const user = computed(() => tg?.initDataUnsafe?.user || null)
  const initData = computed(() => tg?.initData || '')
  const initDataUnsafe = computed(() => tg?.initDataUnsafe || {})
  const colorScheme = computed(() => tg?.colorScheme || 'light')
  const themeParams = computed(() => tg?.themeParams || {})
  const isExpanded = computed(() => tg?.isExpanded || false)
  const viewportHeight = computed(() => tg?.viewportHeight || window.innerHeight)
  const viewportStableHeight = computed(() => tg?.viewportStableHeight || window.innerHeight)

  const ready = () => {
    tg?.ready()
  }

  const expand = () => {
    tg?.expand()
  }

  const close = () => {
    tg?.close()
  }

  const showMainButton = (text, onClick) => {
    if (!tg?.MainButton) return

    tg.MainButton.text = text
    tg.MainButton.show()
    tg.MainButton.onClick(onClick)
  }

  const hideMainButton = () => {
    if (!tg?.MainButton) return
    tg.MainButton.hide()
  }

  const showBackButton = (onClick) => {
    if (!tg?.BackButton) return
    tg.BackButton.show()
    tg.BackButton.onClick(onClick)
  }

  const hideBackButton = () => {
    if (!tg?.BackButton) return
    tg.BackButton.hide()
  }

  const showAlert = (message) => {
    tg?.showAlert(message)
  }

  const showConfirm = (message) => {
    return new Promise((resolve) => {
      tg?.showConfirm(message, resolve)
    })
  }

  const showPopup = (params) => {
    return new Promise((resolve) => {
      tg?.showPopup(params, resolve)
    })
  }

  const setHeaderColor = (color) => {
    tg?.setHeaderColor(color)
  }

  const setBackgroundColor = (color) => {
    tg?.setBackgroundColor(color)
  }

  const enableClosingConfirmation = () => {
    tg?.enableClosingConfirmation()
  }

  const disableClosingConfirmation = () => {
    tg?.disableClosingConfirmation()
  }

  const hapticFeedback = (style = 'light') => {
    tg?.HapticFeedback?.impactOccurred(style)
  }

  const openLink = (url, options = {}) => {
    tg?.openLink(url, options)
  }

  const openTelegramLink = (url) => {
    tg?.openTelegramLink(url)
  }

  const shareUrl = (url, text) => {
    const shareLink = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`
    tg?.openTelegramLink(shareLink)
  }

  return {
    webApp,
    user,
    initData,
    initDataUnsafe,
    colorScheme,
    themeParams,
    isExpanded,
    viewportHeight,
    viewportStableHeight,
    ready,
    expand,
    close,
    showMainButton,
    hideMainButton,
    showBackButton,
    hideBackButton,
    showAlert,
    showConfirm,
    showPopup,
    setHeaderColor,
    setBackgroundColor,
    enableClosingConfirmation,
    disableClosingConfirmation,
    hapticFeedback,
    openLink,
    openTelegramLink,
    shareUrl,
  }
}
