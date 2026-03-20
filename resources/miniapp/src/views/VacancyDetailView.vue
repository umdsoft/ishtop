<template>
  <!-- Loading -->
  <div v-if="loading" class="flex items-center justify-center min-h-screen">
    <LoadingSpinner />
  </div>

  <!-- Not Found -->
  <div v-else-if="!vacancy" class="flex flex-col items-center justify-center min-h-screen px-6">
    <svg class="w-16 h-16 mb-4" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
    </svg>
    <p class="font-medium" style="color: var(--tg-theme-text-color);">{{ t('vacancy.not_found') }}</p>
  </div>

  <!-- Vacancy Detail -->
  <div v-else class="pb-10" style="background-color: var(--tg-theme-bg-color);">

    <!-- Header Section -->
    <div class="px-5 pt-5 pb-4">
      <!-- Badges row -->
      <div v-if="vacancy.is_top || vacancy.is_urgent || vacancy.category" class="flex flex-wrap items-center gap-1.5 mb-3">
        <span v-if="vacancy.is_top" class="badge badge-top">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          TOP
        </span>
        <span v-if="vacancy.is_urgent" class="badge badge-urgent">{{ t('vacancy.urgent_badge') }}</span>
        <span v-if="vacancy.category" class="badge badge-category">{{ getCategoryName(vacancy.category) }}</span>
      </div>

      <!-- Title -->
      <h1 class="text-[20px] font-bold leading-tight mb-2" style="color: var(--tg-theme-text-color);">
        {{ localized('title') || vacancy.title }}
      </h1>

      <!-- Company row -->
      <div class="flex items-center gap-2.5 mb-4">
        <div class="company-avatar">
          <img
            v-if="vacancy.employer?.logo_url"
            :src="vacancy.employer.logo_url"
            class="w-6 h-6 rounded-lg object-cover"
          />
          <div
            v-else
            class="w-6 h-6 rounded-lg flex items-center justify-center"
            style="background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.12);"
          >
            <span class="text-[10px] font-bold" style="color: var(--tg-theme-button-color);">
              {{ getInitial(vacancy.employer?.company_name) }}
            </span>
          </div>
        </div>
        <span class="text-[14px] font-medium" style="color: var(--tg-theme-hint-color);">
          {{ vacancy.employer?.company_name }}
        </span>
        <svg v-if="vacancy.employer?.verification_level === 'verified'" class="w-4 h-4 flex-shrink-0" viewBox="0 0 20 20" fill="var(--tg-theme-button-color)">
          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
      </div>

      <!-- Salary Card -->
      <div class="salary-card mb-3">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-[10px] font-medium mb-0.5" style="color: var(--tg-theme-hint-color);">{{ t('vacancy.salary_label') }}</p>
            <p class="text-[17px] font-bold" style="color: var(--tg-theme-button-color);">
              {{ formatSalary(vacancy) }}
            </p>
          </div>
          <div class="salary-icon">
            <svg class="w-4 h-4" style="color: var(--tg-theme-button-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Info Grid -->
      <div class="grid grid-cols-2 gap-2">
        <div class="info-cell">
          <div class="info-icon" style="background-color: rgba(13, 148, 136, 0.1);">
            <svg class="w-3.5 h-3.5" style="color: #0D9488;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
          </div>
          <div class="min-w-0">
            <p class="info-label">{{ t('vacancy.location') }}</p>
            <p class="info-value truncate">{{ formatLocation(vacancy.city, vacancy.district) || t('vacancy.no_city') }}</p>
          </div>
        </div>
        <div v-if="vacancy.work_type" class="info-cell">
          <div class="info-icon" style="background-color: rgba(16, 185, 129, 0.1);">
            <svg class="w-3.5 h-3.5" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
          </div>
          <div class="min-w-0">
            <p class="info-label">{{ t('vacancy.work_type_label') }}</p>
            <p class="info-value">{{ t(`work_type.${vacancy.work_type}`) }}</p>
          </div>
        </div>
        <div v-if="vacancy.experience_required" class="info-cell">
          <div class="info-icon" style="background-color: rgba(245, 158, 11, 0.1);">
            <svg class="w-3.5 h-3.5" style="color: #f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
            </svg>
          </div>
          <div class="min-w-0">
            <p class="info-label">{{ t('vacancy.experience_label') }}</p>
            <p class="info-value">{{ t(`experience.${vacancy.experience_required}`) }}</p>
          </div>
        </div>
        <div class="info-cell">
          <div class="info-icon" style="background-color: rgba(139, 92, 246, 0.1);">
            <svg class="w-3.5 h-3.5" style="color: #8b5cf6;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
          </div>
          <div class="min-w-0">
            <p class="info-label">{{ t('vacancy.applicants_label') }}</p>
            <p class="info-value">{{ vacancy.applications_count || 0 }} {{ t('common.applicants') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="h-2" style="background-color: var(--tg-theme-secondary-bg-color);"></div>

    <!-- Candidates section (own vacancy, active only) — at the TOP -->
    <VacancyCandidates
      v-if="isOwnVacancy && vacancy.status === 'active'"
      :candidates="candidates"
      :loading="candidatesLoading"
      :locked="candidatesLocked"
      :total-count="candidatesTotalCount"
      :unlock-price="candidatesUnlockPrice"
      :unlocking="unlockingCandidates"
      @unlock="handleUnlockCandidates"
    />

    <!-- Description -->
    <div v-if="descriptionText" class="px-5 py-4">
      <div class="section-header mb-3">
        <div class="section-icon" style="background-color: rgba(13, 148, 136, 0.1);">
          <svg class="w-4 h-4" style="color: #0D9488;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
        </div>
        <h2 class="section-title">{{ t('vacancy.description') }}</h2>
      </div>
      <div class="section-content">
        {{ descriptionText }}
      </div>
    </div>

    <!-- Requirements -->
    <div v-if="requirementsText">
      <div class="h-px mx-5" style="background-color: var(--separator-color, rgba(128,128,128,0.08));"></div>
      <div class="px-5 py-4">
        <div class="section-header mb-3">
          <div class="section-icon" style="background-color: rgba(245, 158, 11, 0.1);">
            <svg class="w-4 h-4" style="color: #f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
          </div>
          <h2 class="section-title">{{ t('vacancy.requirements') }}</h2>
        </div>
        <div class="section-content">
          {{ requirementsText }}
        </div>
      </div>
    </div>

    <!-- Responsibilities -->
    <div v-if="responsibilitiesText">
      <div class="h-px mx-5" style="background-color: var(--separator-color, rgba(128,128,128,0.08));"></div>
      <div class="px-5 py-4">
        <div class="section-header mb-3">
          <div class="section-icon" style="background-color: rgba(16, 185, 129, 0.1);">
            <svg class="w-4 h-4" style="color: #10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
          </div>
          <h2 class="section-title">{{ t('vacancy.responsibilities') }}</h2>
        </div>
        <div class="section-content">
          {{ responsibilitiesText }}
        </div>
      </div>
    </div>

    <!-- Location Mini Map -->
    <div v-if="vacancy.latitude && vacancy.longitude">
      <div class="h-px mx-5" style="background-color: var(--separator-color, rgba(128,128,128,0.08));"></div>
      <div class="px-5 py-4">
        <div class="section-header mb-3">
          <div class="section-icon" style="background-color: rgba(13, 148, 136, 0.1);">
            <svg class="w-4 h-4" style="color: #0D9488;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
            </svg>
          </div>
          <h2 class="section-title">{{ t('vacancy.location_map') }}</h2>
        </div>
        <div
          ref="miniMapContainer"
          class="rounded-xl overflow-hidden"
          style="height: 180px; background-color: var(--tg-theme-secondary-bg-color);"
        ></div>
      </div>
    </div>

    <!-- Divider before company -->
    <div v-if="vacancy.employer" class="h-2" style="background-color: var(--tg-theme-secondary-bg-color);"></div>

    <!-- Company Card -->
    <VacancyCompanyCard v-if="vacancy.employer" :employer="vacancy.employer" />

    <!-- Contact (not for own vacancy) -->
    <div v-if="vacancy.contact_phone && !isOwnVacancy" class="px-5 pb-4">
      <button
        class="contact-btn"
        @click="handlePhoneClick"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
        </svg>
        {{ vacancy.contact_phone }}
      </button>
    </div>

    <!-- Meta info -->
    <div class="px-5 py-3 flex items-center justify-center gap-3">
      <span class="text-[11px]" style="color: var(--tg-theme-hint-color);">
        {{ t('vacancy.published_at') }} {{ formatDate(vacancy.published_at || vacancy.created_at) }}
      </span>
      <span v-if="vacancy.views_count" class="text-[11px]" style="color: var(--tg-theme-hint-color);">
        {{ vacancy.views_count }} {{ t('common.views') }}
      </span>
    </div>

    <!-- Fixed Bottom Actions -->
    <div class="bottom-actions safe-area-bottom">
      <div class="flex gap-2.5">
        <!-- Own vacancy: go to my vacancies -->
        <template v-if="isOwnVacancy">
          <button
            class="flex-1 py-3.5 rounded-xl font-semibold text-[15px] active:scale-[0.97] transition-transform"
            style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
            @click="router.push('/my-vacancies')"
          >
            {{ t('vacancy.own_candidates_go') }}
          </button>
        </template>

        <!-- Not own vacancy: Apply / Applied -->
        <template v-else>
          <button
            v-if="!hasApplied"
            class="flex-1 py-3.5 rounded-xl font-semibold text-[15px] active:scale-[0.97] transition-transform"
            style="background-color: var(--tg-theme-button-color); color: var(--tg-theme-button-text-color);"
            @click="handleApply"
            :disabled="applying"
          >
            {{ applying ? t('vacancy.applying') : t('vacancy.apply_btn') }}
          </button>
          <div
            v-else
            class="flex-1 flex items-center justify-center gap-2 py-3.5 rounded-xl font-semibold text-[15px]"
            style="background-color: rgba(16, 185, 129, 0.12); color: #10b981;"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            {{ t('vacancy.applied') }}
          </div>
        </template>

        <!-- Save Button (not for own vacancy) -->
        <button
          v-if="!isOwnVacancy"
          class="action-btn"
          :class="{ 'action-btn-active': isSaved }"
          @click="handleSave"
        >
          <svg v-if="isSaved" class="w-5 h-5" viewBox="0 0 24 24" fill="#ef4444">
            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
          </svg>
          <svg v-else class="w-5 h-5" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
          </svg>
        </button>

        <!-- Share Button -->
        <button
          class="action-btn"
          @click="handleShare"
        >
          <svg class="w-5 h-5" style="color: var(--tg-theme-hint-color);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useVacancyStore } from '@/stores/vacancy'
import { useAuthStore } from '@/stores/auth'
import { useReferenceStore } from '@/stores/reference'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import { formatSalary as _formatSalary, formatNumber, formatDate, getInitial, formatLocation } from '@/utils/formatters'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import VacancyCandidates from '@/components/VacancyCandidates.vue'
import VacancyCompanyCard from '@/components/VacancyCompanyCard.vue'
import api from '@/utils/api'

const miniMapContainer = ref(null)

const route = useRoute()
const router = useRouter()
const vacancyStore = useVacancyStore()
const authStore = useAuthStore()
const referenceStore = useReferenceStore()
const telegram = useTelegram()
const { t, lang } = useLocale()

const vacancy = ref(null)
const loading = ref(false)
const applying = ref(false)
const hasApplied = ref(false)
const isSaved = ref(false)

// Candidates (for own vacancy)
const candidates = ref([])
const candidatesLoading = ref(false)
const candidatesLocked = ref(true)
const candidatesTotalCount = ref(0)
const candidatesUnlockPrice = ref(0)
const unlockingCandidates = ref(false)

const isOwnVacancy = computed(() => {
  if (!authStore.isAuthenticated || !vacancy.value) return false
  const employerProfile = authStore.user?.employer_profile || authStore.user?.employerProfile
  return employerProfile && vacancy.value.employer_id === employerProfile.id
})

function localized(field) {
  const v = vacancy.value
  if (!v) return ''
  const ru = v[`${field}_ru`]
  const uz = v[`${field}_uz`]
  return lang.value === 'ru' ? (ru || uz) : (uz || ru)
}

const descriptionText = computed(() => localized('description') || vacancy.value?.description || '')
const requirementsText = computed(() => localized('requirements') || vacancy.value?.requirements || '')
const responsibilitiesText = computed(() => localized('responsibilities') || vacancy.value?.responsibilities || '')

onMounted(async () => {
  loading.value = true
  try {
    const [response] = await Promise.all([
      vacancyStore.fetchVacancy(route.params.id),
      referenceStore.loadCategories(),
    ])

    vacancy.value = response.vacancy || response

    if (authStore.isAuthenticated) {
      const employerProfile = authStore.user?.employer_profile || authStore.user?.employerProfile
      if (employerProfile && vacancy.value.employer_id === employerProfile.id) {
        // Own vacancy — load candidates
        loadCandidates()
      } else {
        // Not own — check applications & saved status
        try {
          const myApps = await api.get('/applications/my')
          const apps = myApps.data.applications || myApps.data.data || myApps.data || []
          hasApplied.value = Array.isArray(apps) && apps.some(app => app.vacancy_id === vacancy.value?.id)
        } catch (e) {}

        try {
          const saved = await api.get('/saved')
          const items = saved.data.items || saved.data.data || saved.data || []
          isSaved.value = Array.isArray(items) && items.some(
            item => (item.saveable_type?.includes('Vacancy') && item.saveable_id === vacancy.value?.id)
          )
        } catch (e) {}
      }
    }
  } catch (error) {
    console.error('Failed to load vacancy:', error)
  } finally {
    loading.value = false
  }

  // Mini xarita — vacancy yuklangandan keyin
  if (vacancy.value?.latitude && vacancy.value?.longitude) {
    await nextTick()
    initMiniMap()
  }
})

async function loadCandidates() {
  if (!vacancy.value) return
  candidatesLoading.value = true
  try {
    const res = await api.get(`/vacancies/${vacancy.value.id}/candidates`)
    candidates.value = res.data.candidates || []
    candidatesLocked.value = res.data.locked
    candidatesTotalCount.value = res.data.total_count || 0
    candidatesUnlockPrice.value = res.data.unlock_price || 0
  } catch (e) {
    console.error('Failed to load candidates:', e)
  } finally {
    candidatesLoading.value = false
  }
}

async function handleUnlockCandidates() {
  unlockingCandidates.value = true
  try {
    await api.post(`/vacancies/${vacancy.value.id}/unlock-candidates`)
    telegram.hapticFeedback('success')
    candidatesLocked.value = false
    loadCandidates()
    authStore.fetchUser().catch(() => {})
  } catch (e) {
    const msg = e.response?.data?.message || t('common.error')
    telegram.showAlert(msg)
    telegram.hapticFeedback('error')
  } finally {
    unlockingCandidates.value = false
  }
}

function getCategoryName(slug) {
  return referenceStore.getCategoryName(slug, lang.value)
}

async function handleApply() {
  if (!authStore.isAuthenticated) {
    telegram.showAlert(t('vacancy.login_required'))
    return
  }

  if (vacancy.value.has_questionnaire) {
    const confirm = await telegram.showConfirm(t('vacancy.questionnaire_confirm'))
    if (!confirm) return

    try {
      applying.value = true
      const application = await vacancyStore.applyToVacancy(vacancy.value.id)

      router.push({
        name: 'questionnaire',
        params: { vacancyId: vacancy.value.id },
        query: { applicationId: application.application?.id || application.id },
      })
    } catch (error) {
      telegram.showAlert(error.response?.data?.message || t('common.error'))
    } finally {
      applying.value = false
    }
  } else {
    try {
      applying.value = true
      await vacancyStore.applyToVacancy(vacancy.value.id)
      hasApplied.value = true
      telegram.hapticFeedback('medium')
      telegram.showAlert(t('vacancy.apply_success'))
    } catch (error) {
      telegram.showAlert(error.response?.data?.message || t('common.error'))
    } finally {
      applying.value = false
    }
  }
}

async function handleSave() {
  if (!authStore.isAuthenticated) {
    telegram.showAlert(t('vacancy.login_required'))
    return
  }

  try {
    if (isSaved.value) {
      const saved = await api.get('/saved', { params: { type: 'vacancy' } })
      const items = saved.data.items || saved.data.data || saved.data || []
      const savedItem = Array.isArray(items) && items.find(item => item.saveable_id === vacancy.value.id)
      if (savedItem) {
        await api.delete(`/saved/${savedItem.id}`)
        isSaved.value = false
        telegram.hapticFeedback('soft')
      }
    } else {
      await api.post('/saved', {
        saveable_type: 'vacancy',
        saveable_id: vacancy.value.id,
      })
      isSaved.value = true
      telegram.hapticFeedback('medium')
    }
  } catch (error) {
    telegram.showAlert(t('common.error'))
  }
}

function handleShare() {
  const url = `https://t.me/kadrgobot/app?startapp=vacancy_${vacancy.value.id}`
  const text = `${localized('title') || vacancy.value.title} - ${vacancy.value.employer?.company_name || ''}`
  telegram.shareUrl(url, text)
}

async function handlePhoneClick() {
  const phone = vacancy.value?.contact_phone
  if (!phone) return
  telegram.hapticFeedback('soft')

  const buttonId = await telegram.showPopup({
    title: phone,
    message: t('vacancy.phone_action_hint'),
    buttons: [
      { id: 'call', type: 'default', text: t('vacancy.call_phone') },
      { id: 'copy', type: 'default', text: t('vacancy.copy_phone') },
      { type: 'cancel' },
    ],
  })

  if (buttonId === 'call') {
    window.location.href = `tel:${phone}`
  } else if (buttonId === 'copy') {
    try {
      await navigator.clipboard.writeText(phone)
      telegram.showAlert(t('vacancy.phone_copied'))
    } catch (_) {
      telegram.showAlert(phone)
    }
  }
}

// Mini xarita — Leaflet lazy load
async function initMiniMap() {
  if (!vacancy.value?.latitude || !vacancy.value?.longitude || !miniMapContainer.value) return

  try {
    const [L, leafletCss] = await Promise.all([
      import('leaflet'),
      import('leaflet/dist/leaflet.css'),
    ])

    const lat = parseFloat(vacancy.value.latitude)
    const lng = parseFloat(vacancy.value.longitude)

    const map = L.default.map(miniMapContainer.value, {
      center: [lat, lng],
      zoom: 15,
      zoomControl: false,
      attributionControl: false,
      dragging: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      touchZoom: false,
    })

    L.default.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map)

    // Custom marker
    const icon = L.default.divIcon({
      html: '<div style="width:32px;height:32px;background:#0D9488;border:3px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>',
      className: '',
      iconSize: [32, 32],
      iconAnchor: [16, 16],
    })
    L.default.marker([lat, lng], { icon }).addTo(map)
  } catch (e) {
    console.log('Mini map init failed:', e)
  }
}

function formatSalary(v) {
  return _formatSalary(v, t)
}
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 3px 8px;
  font-size: 11px;
  font-weight: 700;
  border-radius: 6px;
  letter-spacing: 0.2px;
}

.badge-top {
  background-color: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.badge-urgent {
  background-color: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.badge-category {
  font-weight: 600;
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.1);
  color: var(--tg-theme-button-color);
}

.salary-card {
  border-radius: 12px;
  padding: 12px 14px;
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.05);
  border: 1px solid rgba(var(--tg-button-rgb, 13,148,136), 0.08);
}

.salary-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
}

.info-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  border-radius: 10px;
  background-color: var(--tg-theme-secondary-bg-color);
}

.info-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-label {
  font-size: 9px;
  color: var(--tg-theme-hint-color);
  margin-bottom: 1px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.info-value {
  font-size: 12px;
  font-weight: 600;
  color: var(--tg-theme-text-color);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.section-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--tg-theme-text-color);
}

.section-content {
  font-size: 13px;
  line-height: 1.7;
  white-space: pre-line;
  color: var(--tg-theme-text-color);
  opacity: 0.82;
  padding-left: 38px;
}

.contact-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  background-color: var(--tg-theme-secondary-bg-color);
  color: var(--tg-theme-link-color, var(--tg-theme-button-color));
  cursor: pointer;
  transition: transform 0.15s;
}
.contact-btn:active {
  transform: scale(0.98);
}

.bottom-actions {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 30;
  padding: 12px 20px;
  background-color: var(--tg-theme-bg-color);
  border-top: 1px solid var(--separator-color, rgba(128,128,128,0.1));
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.action-btn {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--tg-theme-secondary-bg-color);
  transition: transform 0.15s;
}
.action-btn:active {
  transform: scale(0.95);
}
.action-btn-active {
  background-color: rgba(239, 68, 68, 0.12);
}

.safe-area-bottom {
  padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));
}
</style>
