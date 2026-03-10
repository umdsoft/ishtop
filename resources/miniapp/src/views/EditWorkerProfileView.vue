<template>
  <div class="edit-worker-view" style="background-color: var(--tg-theme-bg-color);">
    <!-- Sticky Header -->
    <div class="view-header">
      <button class="header-back" @click="router.back()">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="header-title">{{ t('edit_profile.worker_title') }}</h1>
      <div class="w-5"></div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-20">
      <LoadingSpinner />
    </div>

    <!-- Form -->
    <div v-else class="px-4 pt-3 pb-14">

      <!-- Section: Personal Info -->
      <div class="section-header">
        <div class="section-icon section-icon-brand">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.personal_info') }}</span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.full_name') }}</label>
          <input v-model="form.full_name" type="text" class="form-input" :placeholder="t('edit_profile.full_name')" required />
        </div>
        <div class="form-divider"></div>
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.specialty') }}</label>
          <input v-model="form.specialty" type="text" class="form-input" :placeholder="t('edit_profile.specialty')" />
        </div>
      </div>

      <!-- Section: Location -->
      <div class="section-header mt-5">
        <div class="section-icon section-icon-indigo">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
          </svg>
        </div>
        <span class="section-title">{{ t('vacancy.location') }}</span>
      </div>

      <div v-if="form.city" class="selected-tags mb-2">
        <span class="selected-tag location-tag">
          {{ form.city }}
          <span v-if="form.district" class="tag-region"> · {{ form.district }}</span>
          <button type="button" class="tag-remove location-tag-remove" @click="clearCity">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <label class="form-label">{{ t('post.region') }}</label>
          <div class="picker-trigger" @click="openPicker('region')">
            <span :class="selectedRegion ? 'picker-value' : 'picker-placeholder'">
              {{ selectedRegion || t('post.select_region') }}
            </span>
            <svg class="picker-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
        </div>
        <div class="form-divider"></div>
        <div class="form-field">
          <label class="form-label">{{ t('post.city') }}</label>
          <div
            class="picker-trigger"
            :class="{ 'picker-trigger-disabled': !selectedRegion }"
            @click="selectedRegion && openPicker('city')"
          >
            <span :class="selectedCityDisplay ? 'picker-value' : 'picker-placeholder'">
              {{ selectedCityDisplay || t('post.select_city') }}
            </span>
            <svg class="picker-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Section: Preferred Categories -->
      <div class="section-header mt-5">
        <div class="section-icon section-icon-teal">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.preferred_categories') }}</span>
      </div>

      <div v-if="form.preferred_categories.length > 0" class="selected-tags mb-2">
        <span v-for="slug in form.preferred_categories" :key="slug" class="selected-tag">
          {{ getCategoryName(slug) }}
          <button type="button" class="tag-remove" @click="removeCategory(slug)">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.preferred_categories') }}</label>
          <div class="picker-trigger" @click="openPicker('category')">
            <span :class="selectedParentCatDisplay ? 'picker-value' : 'picker-placeholder'">
              {{ selectedParentCatDisplay || t('edit_profile.select_category') }}
            </span>
            <svg class="picker-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
        </div>
        <template v-if="selectedParentCat && availableSubcats.length > 0">
          <div class="form-divider"></div>
          <div class="form-field">
            <label class="form-label">{{ t('edit_profile.subcategory') }}</label>
            <div class="picker-trigger" @click="openPicker('subcategory')">
              <span class="picker-placeholder">{{ t('edit_profile.select_subcategory') }}</span>
              <svg class="picker-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
            </div>
          </div>
        </template>
      </div>

      <!-- Section: Experience & Salary -->
      <div class="section-header mt-5">
        <div class="section-icon section-icon-green">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.experience_years') }}</span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.experience_years') }}</label>
          <input v-model.number="form.experience_years" type="number" min="0" max="50" class="form-input" placeholder="0" />
        </div>
        <div class="form-divider"></div>
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.expected_salary_min') }}</label>
          <div class="salary-row">
            <input type="text" inputmode="numeric" :value="salaryMinDisplay" @input="onSalaryInput('expected_salary_min', $event)" class="form-input flex-1" :placeholder="t('search.salary_from')" />
            <span class="salary-sep">—</span>
            <input type="text" inputmode="numeric" :value="salaryMaxDisplay" @input="onSalaryInput('expected_salary_max', $event)" class="form-input flex-1" :placeholder="t('search.salary_to')" />
          </div>
        </div>
      </div>

      <!-- Section: Work Type -->
      <div class="section-header mt-5">
        <div class="section-icon section-icon-amber">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.work_type') }}</span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <div class="work-type-grid">
            <button
              v-for="wt in workTypes"
              :key="wt.value"
              type="button"
              class="wt-chip"
              :class="{ 'wt-chip-active': form.work_types.includes(wt.value) }"
              @click="toggleWorkType(wt.value)"
            >
              <svg v-if="form.work_types.includes(wt.value)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
              {{ wt.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Section: Skills -->
      <div class="section-header mt-5">
        <div class="section-icon" style="background-color: rgba(236,72,153,0.12); color: #ec4899;">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.skills') }}</span>
      </div>

      <div v-if="form.skills.length > 0" class="selected-tags mb-2">
        <span v-for="(skill, idx) in form.skills" :key="idx" class="selected-tag skill-tag">
          {{ skill }}
          <button type="button" class="tag-remove skill-tag-remove" @click="removeSkill(idx)">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.skills') }}</label>
          <div class="picker-trigger" @click="openPicker('skills')">
            <span class="picker-placeholder">
              {{ form.preferred_categories.length > 0 ? t('edit_profile.skills_select') : t('edit_profile.skills_select_category_first') }}
            </span>
            <svg class="picker-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
          </div>
        </div>
        <div class="form-divider"></div>
        <div class="form-field">
          <label class="form-label">{{ t('edit_profile.skills_add_custom') }}</label>
          <div class="skill-input-row">
            <input
              v-model="skillInput"
              type="text"
              class="form-input flex-1"
              :placeholder="t('edit_profile.skills_placeholder')"
              maxlength="100"
              @keydown.enter.prevent="addSkill"
            />
            <button
              v-if="skillInput.trim()"
              type="button"
              class="skill-add-btn"
              @click="addSkill"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Section: Bio -->
      <div class="section-header mt-5">
        <div class="section-icon section-icon-purple">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.bio') }}</span>
      </div>

      <div class="form-card">
        <div class="form-field">
          <textarea v-model="form.bio" rows="4" class="form-input form-textarea" :placeholder="t('edit_profile.bio')"></textarea>
        </div>
      </div>

      <!-- Section: Work Experience -->
      <div class="section-header mt-5">
        <div class="section-icon section-icon-orange">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
          </svg>
        </div>
        <span class="section-title">{{ t('edit_profile.work_experience') }}</span>
      </div>

      <!-- Existing experience entries -->
      <div v-for="(exp, idx) in form.work_experience" :key="idx" class="exp-card">
        <div class="exp-card-header">
          <div class="exp-card-num">{{ idx + 1 }}</div>
          <button type="button" class="exp-card-remove" @click="removeExperience(idx)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
          </button>
        </div>
        <div class="form-card">
          <div class="form-field">
            <label class="form-label">{{ t('edit_profile.work_exp_company') }}</label>
            <input v-model="exp.company" type="text" class="form-input" :placeholder="t('edit_profile.work_exp_company')" />
          </div>
          <div class="form-divider"></div>
          <div class="form-field">
            <label class="form-label">{{ t('edit_profile.work_exp_position') }}</label>
            <input v-model="exp.position" type="text" class="form-input" :placeholder="t('edit_profile.work_exp_position')" />
          </div>
          <div class="form-divider"></div>
          <div class="form-field">
            <label class="form-label">{{ t('edit_profile.work_exp_start') }} — {{ t('edit_profile.work_exp_end') }}</label>
            <div class="date-row">
              <div class="date-trigger" @click="openDatePicker(idx, 'start_date')">
                <span :class="exp.start_date ? 'date-value' : 'date-placeholder'">
                  {{ exp.start_date ? formatMonthYear(exp.start_date) : t('edit_profile.work_exp_start') }}
                </span>
                <svg class="date-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
              </div>
              <span class="salary-sep">—</span>
              <div v-if="!exp.is_current" class="date-trigger" @click="openDatePicker(idx, 'end_date')">
                <span :class="exp.end_date ? 'date-value' : 'date-placeholder'">
                  {{ exp.end_date ? formatMonthYear(exp.end_date) : t('edit_profile.work_exp_end') }}
                </span>
                <svg class="date-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
              </div>
              <span v-else class="current-badge">{{ t('edit_profile.work_exp_current') }}</span>
            </div>
            <label class="current-check" @click="exp.is_current = !exp.is_current">
              <div class="check-box" :class="{ 'check-box-active': exp.is_current }">
                <svg v-if="exp.is_current" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </div>
              <span class="current-check-text">{{ t('edit_profile.work_exp_current') }}</span>
            </label>
          </div>
          <div class="form-divider"></div>
          <div class="form-field">
            <label class="form-label">{{ t('edit_profile.work_exp_description') }}</label>
            <textarea v-model="exp.description" rows="2" class="form-input form-textarea" :placeholder="t('edit_profile.work_exp_description')"></textarea>
          </div>
        </div>
      </div>

      <!-- Add experience button -->
      <button v-if="form.work_experience.length < 10" type="button" class="add-exp-btn" @click="addExperience">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        {{ t('edit_profile.work_exp_add') }}
      </button>
    </div>

    <!-- Fixed Save Button -->
    <div v-if="!loading" class="save-bar">
      <button class="save-btn" :disabled="saving" @click="handleSave">
        <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span v-if="saving" class="save-spinner"></span>
        {{ saving ? t('common.loading') : t('common.save') }}
      </button>
    </div>

    <!-- Bottom Sheet Picker -->
    <transition name="sheet">
      <div v-if="activePicker" class="sheet-backdrop" @click="activePicker = null">
        <div class="sheet-panel" @click.stop>
          <div class="sheet-handle"></div>
          <div class="sheet-title">{{ pickerTitle }}</div>

          <div v-if="pickerOptions.length > 10" class="sheet-search">
            <svg class="sheet-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input
              v-model="pickerSearch"
              class="sheet-search-input"
              :placeholder="t('home.search_placeholder')"
            />
          </div>

          <div class="sheet-options">
            <div
              v-for="opt in pickerOptions"
              :key="opt.value"
              class="sheet-option"
              :class="{ 'sheet-option-active': isPickerActive(opt) }"
              @click="onPickerSelect(opt)"
            >
              <div class="sheet-option-content">
                <span class="sheet-option-label">{{ opt.label }}</span>
                <span v-if="opt.badge" class="sheet-option-badge" :class="opt.badgeClass">{{ opt.badge }}</span>
              </div>
              <svg v-if="isPickerActive(opt)" class="sheet-option-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>
            <div v-if="pickerOptions.length === 0" class="sheet-empty">
              {{ t('search.no_results') }}
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Date Picker Bottom Sheet -->
    <transition name="sheet">
      <div v-if="datePickerTarget" class="sheet-backdrop" @click="datePickerTarget = null">
        <div class="sheet-panel date-picker-panel" @click.stop>
          <div class="sheet-handle"></div>
          <div class="sheet-title">{{ t('edit_profile.work_exp_select_date') }}</div>

          <!-- Year selector -->
          <div class="dp-year-row">
            <button type="button" class="dp-year-btn" @click="dpYear--">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
              </svg>
            </button>
            <span class="dp-year-label">{{ dpYear }}</span>
            <button type="button" class="dp-year-btn" :disabled="dpYear >= currentYear" @click="dpYear++">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </button>
          </div>

          <!-- Month grid -->
          <div class="dp-month-grid">
            <button
              v-for="(mName, mIdx) in monthNames"
              :key="mIdx"
              type="button"
              class="dp-month-cell"
              :class="{
                'dp-month-active': dpMonth === mIdx,
                'dp-month-disabled': isMonthDisabled(mIdx),
              }"
              :disabled="isMonthDisabled(mIdx)"
              @click="selectMonth(mIdx)"
            >
              {{ mName }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProfileStore } from '@/stores/profile'
import { useReferenceStore } from '@/stores/reference'
import { useTelegram } from '@/composables/useTelegram'
import { useLocale } from '@/composables/useLocale'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import api from '@/utils/api'
import { formatNumber } from '@/utils/formatters'

const router = useRouter()
const profileStore = useProfileStore()
const referenceStore = useReferenceStore()
const telegram = useTelegram()
const { t, lang } = useLocale()

function lName(obj) {
  return lang.value === 'ru' ? (obj.name_ru || obj.name_uz) : (obj.name_uz || obj.name_ru)
}

const loading = ref(false)
const saving = ref(false)

const form = ref({
  full_name: '',
  specialty: '',
  city: '',
  district: '',
  experience_years: 0,
  expected_salary_min: null,
  expected_salary_max: null,
  work_types: [],
  preferred_categories: [],
  skills: [],
  bio: '',
  work_experience: [],
})

const skillInput = ref('')

const workTypes = computed(() => [
  { value: 'full_time', label: t('work_type.full_time') },
  { value: 'part_time', label: t('work_type.part_time') },
  { value: 'remote', label: t('work_type.remote') },
  { value: 'temporary', label: t('work_type.temporary') },
])

const suggestedSkills = computed(() => {
  const selectedSlugs = form.value.preferred_categories
  if (!selectedSlugs.length) return []

  const seen = new Set()
  const result = []

  for (const slug of selectedSlugs) {
    for (const cat of referenceStore.categories) {
      const match = cat.slug === slug ? cat : cat.children?.find(c => c.slug === slug)
      if (match?.default_skills) {
        for (const skill of match.default_skills) {
          const name = lang.value === 'ru' ? (skill.ru || skill.uz) : (skill.uz || skill.ru)
          const key = (skill.uz || skill.ru || '').toLowerCase()
          if (!key || seen.has(key)) continue
          seen.add(key)
          result.push({ uz: skill.uz, ru: skill.ru, name })
        }
      }
    }
  }

  return result
})

function toggleWorkType(value) {
  telegram.hapticFeedback('soft')
  const idx = form.value.work_types.indexOf(value)
  if (idx >= 0) form.value.work_types.splice(idx, 1)
  else form.value.work_types.push(value)
}

function addSkill() {
  const val = skillInput.value.trim()
  if (!val || form.value.skills.includes(val)) return
  telegram.hapticFeedback('soft')
  form.value.skills.push(val)
  skillInput.value = ''
}

function removeSkill(idx) {
  telegram.hapticFeedback('soft')
  form.value.skills.splice(idx, 1)
}

// ── Salary formatting ──
const salaryMinDisplay = computed(() => {
  return form.value.expected_salary_min ? formatNumber(form.value.expected_salary_min) : ''
})
const salaryMaxDisplay = computed(() => {
  return form.value.expected_salary_max ? formatNumber(form.value.expected_salary_max) : ''
})
function onSalaryInput(field, event) {
  const raw = event.target.value.replace(/\D/g, '')
  const num = raw ? parseInt(raw, 10) : null
  form.value[field] = num
  event.target.value = num ? formatNumber(num) : ''
}

// ── Work Experience ──
function addExperience() {
  telegram.hapticFeedback('soft')
  form.value.work_experience.push({
    company: '',
    position: '',
    start_date: '',
    end_date: '',
    is_current: false,
    description: '',
  })
}

function removeExperience(idx) {
  telegram.hapticFeedback('soft')
  form.value.work_experience.splice(idx, 1)
}

// ── Date Picker ──
const currentYear = new Date().getFullYear()
const datePickerTarget = ref(null) // { expIdx, field }
const dpYear = ref(currentYear)
const dpMonth = ref(-1)

const monthNames = computed(() => t('edit_profile.months_short'))

function formatMonthYear(dateStr) {
  if (!dateStr) return ''
  const [y, m] = dateStr.split('-').map(Number)
  const months = t('edit_profile.months')
  return `${months[m - 1]} ${y}`
}

function openDatePicker(expIdx, field) {
  telegram.hapticFeedback('soft')
  const current = form.value.work_experience[expIdx][field]
  if (current) {
    const [y, m] = current.split('-').map(Number)
    dpYear.value = y
    dpMonth.value = m - 1
  } else {
    dpYear.value = currentYear
    dpMonth.value = -1
  }
  datePickerTarget.value = { expIdx, field }
}

function isMonthDisabled(mIdx) {
  return dpYear.value === currentYear && mIdx > new Date().getMonth()
}

function selectMonth(mIdx) {
  if (isMonthDisabled(mIdx)) return
  telegram.hapticFeedback('medium')
  const val = `${dpYear.value}-${String(mIdx + 1).padStart(2, '0')}`
  const { expIdx, field } = datePickerTarget.value
  form.value.work_experience[expIdx][field] = val
  dpMonth.value = mIdx
  datePickerTarget.value = null
}

// ── Location ──
const selectedRegion = ref('')
const selectedCityId = ref('')

const regions = computed(() => {
  const set = new Set()
  for (const city of referenceStore.cities) {
    if (city.region) set.add(city.region)
  }
  return [...set].sort((a, b) => a.localeCompare(b))
})

const filteredCities = computed(() => {
  if (!selectedRegion.value) return []
  return referenceStore.cities
    .filter(c => c.region === selectedRegion.value)
    .sort((a, b) => a.name_uz.localeCompare(b.name_uz))
})

const selectedCityDisplay = computed(() => {
  if (!selectedCityId.value) return ''
  const city = referenceStore.cities.find(c => c.id === selectedCityId.value)
  if (!city) return ''
  return `${lName(city)} (${city.type === 'shahar' ? t('post.type_shahar') : t('post.type_tuman')})`
})

function clearCity() {
  telegram.hapticFeedback('soft')
  form.value.city = ''
  form.value.district = ''
  selectedRegion.value = ''
  selectedCityId.value = ''
}

// ── Categories ──
const selectedParentCat = ref('')

const selectedParentCatDisplay = computed(() => {
  if (!selectedParentCat.value) return ''
  const cat = referenceStore.categories.find(c => c.slug === selectedParentCat.value)
  return cat ? (lName(cat) || cat.slug) : ''
})

const availableSubcats = computed(() => {
  if (!selectedParentCat.value) return []
  const parent = referenceStore.categories.find(c => c.slug === selectedParentCat.value)
  if (!parent?.children?.length) return []
  return parent.children.filter(c => !form.value.preferred_categories.includes(c.slug))
})

function addCategory(slug) {
  if (!slug || form.value.preferred_categories.includes(slug)) return
  telegram.hapticFeedback('soft')
  form.value.preferred_categories.push(slug)
}

function removeCategory(slug) {
  telegram.hapticFeedback('soft')
  const idx = form.value.preferred_categories.indexOf(slug)
  if (idx >= 0) form.value.preferred_categories.splice(idx, 1)
}

function getCategoryName(slug) {
  for (const cat of referenceStore.categories) {
    if (cat.slug === slug) return lName(cat) || cat.slug
    if (cat.children) {
      for (const child of cat.children) {
        if (child.slug === slug) return lName(child) || child.slug
      }
    }
  }
  return slug
}

// ── Bottom Sheet Picker ──
const activePicker = ref(null)
const pickerSearch = ref('')

const pickerTitle = computed(() => {
  switch (activePicker.value) {
    case 'region': return t('post.region')
    case 'city': return t('post.city')
    case 'category': return t('edit_profile.preferred_categories')
    case 'subcategory': return t('edit_profile.subcategory')
    case 'skills': return t('edit_profile.skills')
    default: return ''
  }
})

const pickerOptions = computed(() => {
  const q = pickerSearch.value.toLowerCase()
  switch (activePicker.value) {
    case 'region':
      return regions.value
        .filter(r => !q || r.toLowerCase().includes(q))
        .map(r => ({ value: r, label: r }))
    case 'city':
      return filteredCities.value
        .filter(c => !q || lName(c).toLowerCase().includes(q))
        .map(c => ({
          value: c.id,
          label: lName(c),
          badge: c.type === 'shahar' ? t('post.type_shahar') : t('post.type_tuman'),
          badgeClass: c.type === 'shahar' ? 'badge-shahar' : 'badge-tuman',
        }))
    case 'category':
      return referenceStore.categories
        .filter(c => !q || lName(c).toLowerCase().includes(q))
        .map(c => ({
          value: c.slug,
          label: lName(c) || c.slug,
          badge: c.children?.length ? `${c.children.length}` : null,
          badgeClass: 'badge-count',
        }))
    case 'subcategory':
      return availableSubcats.value.map(c => ({
        value: c.slug,
        label: lName(c) || c.slug,
      }))
    case 'skills':
      return suggestedSkills.value
        .filter(s => !q || s.name.toLowerCase().includes(q))
        .map(s => ({
          value: s.uz,
          label: s.name,
        }))
    default: return []
  }
})

function openPicker(type) {
  pickerSearch.value = ''
  activePicker.value = type
}

function isPickerActive(opt) {
  switch (activePicker.value) {
    case 'region': return selectedRegion.value === opt.value
    case 'city': return selectedCityId.value === opt.value
    case 'category': return selectedParentCat.value === opt.value
    case 'subcategory': return form.value.preferred_categories.includes(opt.value)
    case 'skills': {
      const name = lang.value === 'ru' ? (suggestedSkills.value.find(s => s.uz === opt.value)?.ru || opt.label) : opt.label
      return form.value.skills.includes(name)
    }
    default: return false
  }
}

function onPickerSelect(opt) {
  telegram.hapticFeedback('soft')
  switch (activePicker.value) {
    case 'region':
      selectedRegion.value = opt.value
      selectedCityId.value = ''
      activePicker.value = null
      setTimeout(() => openPicker('city'), 250)
      break
    case 'city': {
      selectedCityId.value = opt.value
      const city = referenceStore.cities.find(c => c.id === opt.value)
      if (city) {
        form.value.city = city.name_uz  // Always store name_uz in DB
        form.value.district = city.region || ''
      }
      activePicker.value = null
      break
    }
    case 'category': {
      selectedParentCat.value = opt.value
      const parent = referenceStore.categories.find(c => c.slug === opt.value)
      if (parent && (!parent.children || parent.children.length === 0)) {
        addCategory(parent.slug)
        selectedParentCat.value = ''
        activePicker.value = null
      } else {
        activePicker.value = null
        setTimeout(() => openPicker('subcategory'), 250)
      }
      break
    }
    case 'subcategory':
      addCategory(opt.value)
      if (availableSubcats.value.length <= 1) {
        activePicker.value = null
      }
      break
    case 'skills': {
      const skillObj = suggestedSkills.value.find(s => s.uz === opt.value)
      const name = skillObj ? (lang.value === 'ru' ? (skillObj.ru || skillObj.uz) : (skillObj.uz || skillObj.ru)) : opt.label
      const idx = form.value.skills.indexOf(name)
      if (idx >= 0) {
        form.value.skills.splice(idx, 1)
      } else {
        form.value.skills.push(name)
      }
      break
    }
  }
}

// ── Lifecycle ──
onMounted(async () => {
  loading.value = true
  try {
    referenceStore.categoriesLoaded = false
    await referenceStore.loadAll()
    await profileStore.fetchWorkerProfile()
    if (profileStore.workerProfile) {
      const p = profileStore.workerProfile
      form.value = {
        full_name: p.full_name || '',
        specialty: p.specialty || '',
        city: p.city || '',
        district: p.district || '',
        experience_years: p.experience_years || 0,
        expected_salary_min: p.expected_salary_min || null,
        expected_salary_max: p.expected_salary_max || null,
        work_types: p.work_types || [],
        preferred_categories: p.preferred_categories || [],
        skills: p.skills || [],
        bio: p.bio || '',
        work_experience: (p.work_experience || []).map(e => ({
          ...e,
          is_current: !e.end_date,
        })),
      }
      if (form.value.city) {
        const mc = referenceStore.cities.find(c => c.name_uz === form.value.city)
        if (mc) {
          selectedRegion.value = mc.region || ''
          selectedCityId.value = mc.id
          if (!form.value.district) form.value.district = mc.region || ''
        }
      }
    }
  } catch (error) {
    console.error('Failed to load profile:', error)
  } finally {
    loading.value = false
  }
})

async function handleSave() {
  saving.value = true
  try {
    const payload = {
      ...form.value,
      work_experience: form.value.work_experience
        .filter(e => e.company || e.position)
        .map(({ is_current, ...e }) => ({
          ...e,
          end_date: is_current ? null : (e.end_date || null),
        })),
    }
    await api.put('/profile/worker', payload)
    await profileStore.fetchWorkerProfile()
    telegram.showAlert(t('edit_profile.save_success'))
    telegram.hapticFeedback('medium')
    router.back()
  } catch (error) {
    telegram.showAlert(error.response?.data?.message || t('edit_profile.save_error'))
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.edit-worker-view {
  min-height: 100vh;
}

/* Header */
.view-header {
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background-color: var(--tg-theme-bg-color);
  border-bottom: 1px solid var(--separator-color, rgba(128,128,128,0.06));
}
.header-back {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  color: var(--tg-theme-button-color);
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
  transition: transform 0.15s;
}
.header-back:active { transform: scale(0.92); }
.header-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--tg-theme-text-color);
}

/* Section Headers */
.section-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.section-icon {
  width: 26px;
  height: 26px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.section-icon-brand { background-color: rgba(13,148,136,0.12); color: #0D9488; }
.section-icon-indigo { background-color: rgba(99,102,241,0.12); color: #6366f1; }
.section-icon-teal { background-color: rgba(20,184,166,0.12); color: #14b8a6; }
.section-icon-green { background-color: rgba(34,197,94,0.12); color: #22c55e; }
.section-icon-amber { background-color: rgba(245,158,11,0.12); color: #f59e0b; }
.section-icon-purple { background-color: rgba(139,92,246,0.12); color: #8b5cf6; }
.section-icon-orange { background-color: rgba(249,115,22,0.12); color: #f97316; }
.section-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: var(--tg-theme-hint-color);
}

/* Form Card */
.form-card {
  border-radius: 14px;
  background-color: var(--tg-theme-secondary-bg-color);
  overflow: hidden;
}
.form-field { padding: 10px 14px; }
.form-divider {
  height: 1px;
  margin-left: 14px;
  background-color: var(--separator-color, rgba(128,128,128,0.08));
}
.form-label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  color: var(--tg-theme-hint-color);
  margin-bottom: 4px;
}
.form-input {
  width: 100%;
  padding: 8px 0;
  font-size: 14px;
  font-weight: 500;
  color: var(--tg-theme-text-color);
  background: transparent;
  border: none;
  outline: none;
}
.form-input::placeholder {
  color: var(--tg-theme-hint-color);
  opacity: 0.45;
}
.form-textarea { resize: none; line-height: 1.5; }

/* Picker Trigger */
.picker-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 0;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}
.picker-trigger:active { opacity: 0.7; }
.picker-trigger-disabled {
  opacity: 0.35;
  pointer-events: none;
}
.picker-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--tg-theme-text-color);
}
.picker-placeholder {
  font-size: 14px;
  font-weight: 500;
  color: var(--tg-theme-hint-color);
  opacity: 0.5;
}
.picker-chevron {
  width: 16px;
  height: 16px;
  color: var(--tg-theme-hint-color);
  opacity: 0.4;
  flex-shrink: 0;
}

/* Salary Row */
.salary-row { display: flex; align-items: center; gap: 8px; }
.salary-sep { font-size: 14px; color: var(--tg-theme-hint-color); opacity: 0.5; flex-shrink: 0; }

/* Selected Tags */
.selected-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.selected-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 8px 5px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  background-color: rgba(20,184,166,0.1);
  color: #14b8a6;
  white-space: nowrap;
}
.tag-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 5px;
  color: #14b8a6;
  opacity: 0.6;
}
.tag-remove:active { opacity: 1; }
.location-tag { background-color: rgba(99,102,241,0.1) !important; color: #6366f1 !important; }
.location-tag-remove { color: #6366f1 !important; }
.tag-region { opacity: 0.6; font-weight: 500; }
.skill-tag { background-color: rgba(236,72,153,0.1) !important; color: #ec4899 !important; }
.skill-tag-remove { color: #ec4899 !important; }
.skill-input-row { display: flex; align-items: center; gap: 8px; }
.skill-add-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background-color: rgba(236,72,153,0.1);
  color: #ec4899;
  transition: transform 0.15s;
}
.skill-add-btn:active { transform: scale(0.9); }

/* Work Type Chips */
.work-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.wt-chip {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-hint-color);
  transition: all 0.15s;
}
.wt-chip:active { transform: scale(0.96); }
.wt-chip-active {
  border-color: rgba(var(--tg-button-rgb, 13,148,136), 0.35);
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
  color: var(--tg-theme-button-color);
}

/* Save Bar */
.save-bar {
  position: fixed;
  bottom: calc(56px + env(safe-area-inset-bottom, 0px));
  left: 0;
  right: 0;
  z-index: 20;
  padding: 10px 16px;
  background-color: var(--tg-theme-bg-color);
  border-top: 1px solid var(--separator-color, rgba(128,128,128,0.06));
}
.save-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  padding: 13px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
  transition: transform 0.15s, opacity 0.15s;
}
.save-btn:active { transform: scale(0.98); }
.save-btn:disabled { opacity: 0.6; }
.save-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Bottom Sheet Picker ── */
.sheet-backdrop {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: flex-end;
}
.sheet-panel {
  width: 100%;
  max-height: 72vh;
  background-color: var(--tg-theme-bg-color);
  border-radius: 18px 18px 0 0;
  display: flex;
  flex-direction: column;
  padding-bottom: max(12px, env(safe-area-inset-bottom));
}
.sheet-handle {
  width: 36px;
  height: 4px;
  border-radius: 2px;
  background: rgba(128,128,128,0.25);
  margin: 10px auto 0;
}
.sheet-title {
  padding: 14px 20px 8px;
  font-size: 17px;
  font-weight: 700;
  color: var(--tg-theme-text-color);
}

/* Search */
.sheet-search {
  position: relative;
  padding: 0 16px 8px;
}
.sheet-search-icon {
  position: absolute;
  left: 28px;
  top: 50%;
  transform: translateY(calc(-50% - 4px));
  width: 16px;
  height: 16px;
  color: var(--tg-theme-hint-color);
  opacity: 0.5;
}
.sheet-search-input {
  width: 100%;
  padding: 10px 14px 10px 36px;
  border-radius: 10px;
  background-color: var(--tg-theme-secondary-bg-color);
  border: none;
  outline: none;
  font-size: 14px;
  font-weight: 500;
  color: var(--tg-theme-text-color);
}
.sheet-search-input::placeholder {
  color: var(--tg-theme-hint-color);
  opacity: 0.5;
}

/* Options */
.sheet-options {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}
.sheet-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 13px 20px;
  cursor: pointer;
  transition: background 0.1s;
}
.sheet-option:active {
  background-color: rgba(128,128,128,0.06);
}
.sheet-option-content {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  flex: 1;
}
.sheet-option-label {
  font-size: 15px;
  font-weight: 500;
  color: var(--tg-theme-text-color);
}
.sheet-option-active .sheet-option-label {
  color: var(--tg-theme-button-color);
  font-weight: 600;
}
.sheet-option-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 5px;
  flex-shrink: 0;
}
.badge-shahar { background-color: rgba(13,148,136,0.1); color: #0D9488; }
.badge-tuman { background-color: rgba(245,158,11,0.1); color: #f59e0b; }
.badge-count { background-color: rgba(128,128,128,0.08); color: var(--tg-theme-hint-color); }
.sheet-option-check {
  width: 18px;
  height: 18px;
  color: var(--tg-theme-button-color);
  flex-shrink: 0;
}
.sheet-empty {
  padding: 32px 20px;
  text-align: center;
  font-size: 14px;
  color: var(--tg-theme-hint-color);
  opacity: 0.6;
}

/* Work Experience */
.exp-card {
  margin-bottom: 12px;
}
.exp-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
  padding: 0 2px;
}
.exp-card-num {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  background-color: rgba(249,115,22,0.12);
  color: #f97316;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.exp-card-remove {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ef4444;
  opacity: 0.6;
  transition: opacity 0.15s;
}
.exp-card-remove:active { opacity: 1; }

.date-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.current-badge {
  flex: 1;
  font-size: 12px;
  font-weight: 600;
  color: #22c55e;
  text-align: center;
}
.current-check {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}
.check-box {
  width: 20px;
  height: 20px;
  border-radius: 6px;
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.2));
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
  flex-shrink: 0;
}
.check-box-active {
  background-color: #22c55e;
  border-color: #22c55e;
  color: white;
}
.current-check-text {
  font-size: 13px;
  font-weight: 500;
  color: var(--tg-theme-hint-color);
}

.add-exp-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  padding: 13px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  background-color: rgba(249,115,22,0.1);
  color: #f97316;
  transition: all 0.15s;
}
.add-exp-btn:active { transform: scale(0.96); background-color: rgba(249,115,22,0.18); }

/* Date Trigger */
.date-trigger {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
  border-radius: 10px;
  background-color: var(--tg-theme-bg-color);
  border: 1.5px solid var(--separator-color, rgba(128,128,128,0.12));
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  transition: border-color 0.15s;
}
.date-trigger:active {
  border-color: var(--tg-theme-button-color);
}
.date-value {
  font-size: 13px;
  font-weight: 600;
  color: var(--tg-theme-text-color);
}
.date-placeholder {
  font-size: 13px;
  font-weight: 500;
  color: var(--tg-theme-hint-color);
  opacity: 0.5;
}
.date-icon {
  width: 14px;
  height: 14px;
  color: var(--tg-theme-hint-color);
  opacity: 0.4;
  flex-shrink: 0;
}

/* Date Picker Panel */
.date-picker-panel {
  max-height: 50vh;
}
.dp-year-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
  padding: 4px 20px 12px;
}
.dp-year-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--tg-theme-button-color);
  background-color: rgba(var(--tg-button-rgb, 13,148,136), 0.08);
  transition: transform 0.15s;
}
.dp-year-btn:active { transform: scale(0.9); }
.dp-year-btn:disabled { opacity: 0.3; pointer-events: none; }
.dp-year-label {
  font-size: 20px;
  font-weight: 800;
  color: var(--tg-theme-text-color);
  min-width: 60px;
  text-align: center;
}

.dp-month-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  padding: 0 16px 16px;
}
.dp-month-cell {
  padding: 12px 8px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  color: var(--tg-theme-text-color);
  background-color: var(--tg-theme-secondary-bg-color);
  transition: all 0.15s;
}
.dp-month-cell:active { transform: scale(0.95); }
.dp-month-active {
  background-color: var(--tg-theme-button-color) !important;
  color: var(--tg-theme-button-text-color) !important;
}
.dp-month-disabled {
  opacity: 0.25;
  pointer-events: none;
}

/* Sheet Transitions */
.sheet-enter-active { transition: opacity 0.2s ease-out; }
.sheet-enter-active .sheet-panel { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.sheet-leave-active { transition: opacity 0.15s ease-in; }
.sheet-leave-active .sheet-panel { transition: transform 0.2s ease-in; }
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.sheet-enter-from .sheet-panel, .sheet-leave-to .sheet-panel { transform: translateY(100%); }

</style>
