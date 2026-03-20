<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingSpinner size="lg" text="Ariza yuklanmoqda..." />
    </div>

    <!-- Not Found -->
    <div v-else-if="!application" class="text-center py-20">
      <ClipboardDocumentListIcon class="h-16 w-16 mx-auto text-surface-400 dark:text-surface-500 mb-4" />
      <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100 mb-2">
        Ariza topilmadi
      </h3>
      <p class="text-surface-600 dark:text-surface-400 mb-4">
        Bunday ariza mavjud emas yoki o'chirilgan
      </p>
      <AppButton variant="primary" @click="$router.push({ name: 'vacancy-detail', params: { id: route.params.id } })">
        Orqaga qaytish
      </AppButton>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3 min-w-0">
            <button
              class="p-2 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors shrink-0"
              @click="$router.push({ name: 'vacancy-detail', params: { id: route.params.id } })"
            >
              <ArrowLeftIcon class="h-5 w-5 text-surface-600 dark:text-surface-400" />
            </button>

            <!-- Avatar -->
            <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-lg shrink-0">
              {{ applicantInitials }}
            </div>

            <div class="min-w-0">
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">
                  {{ applicantName }}
                </h1>
                <AppBadge :variant="getStageVariant(application.stage)" size="sm">
                  {{ getStageLabel(application.stage) }}
                </AppBadge>
              </div>
              <p class="text-surface-600 dark:text-surface-400 text-sm mt-0.5">
                <span v-if="application.worker?.specialty">{{ application.worker.specialty }}</span>
                <span v-if="application.vacancy"> &bull; {{ application.vacancy.title_uz || application.vacancy.title_ru }}</span>
              </p>
            </div>
          </div>

          <!-- Rating Stars in header -->
          <div class="flex items-center gap-1 shrink-0">
            <button
              v-for="star in 5"
              :key="star"
              class="p-0.5 transition-transform hover:scale-110"
              @click="setRating(star)"
            >
              <StarIconSolid
                v-if="star <= (application.recruiter_rating || 0)"
                class="h-6 w-6 text-warning-400"
              />
              <StarIcon
                v-else
                class="h-6 w-6 text-surface-300 dark:text-surface-600"
              />
            </button>
          </div>
        </div>
      </div>

      <!-- Two-column layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Candidate Info -->
          <AppCard>
            <template #header>
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                  Nomzod haqida
                </h3>
                <a
                  v-if="application.worker?.resume_file_url"
                  :href="application.worker.resume_file_url"
                  target="_blank"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950/30 rounded-lg hover:bg-brand-100 dark:hover:bg-brand-900/30 transition-colors"
                >
                  <DocumentArrowDownIcon class="h-4 w-4" />
                  Resume yuklab olish
                </a>
              </div>
            </template>

            <div class="space-y-3">
              <div v-if="application.worker?.full_name" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Ism</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.worker.full_name }}</span>
              </div>
              <div v-if="application.worker?.birth_date" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Tug'ilgan sana</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">
                  {{ formatDate(application.worker.birth_date) }}
                  <span class="text-surface-400 dark:text-surface-500">({{ getAge(application.worker.birth_date) }} yosh)</span>
                </span>
              </div>
              <div v-if="application.worker?.gender" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Jins</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.worker.gender === 'male' ? 'Erkak' : 'Ayol' }}</span>
              </div>
              <div v-if="application.worker?.specialty" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Mutaxassislik</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.worker.specialty }}</span>
              </div>
              <div v-if="application.worker?.city" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Manzil</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">
                  {{ formatLocation(application.worker.city, application.worker.district) }}
                </span>
              </div>
              <div v-if="application.worker?.experience_years" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Tajriba</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.worker.experience_years }} yil</span>
              </div>
              <div v-if="application.worker?.education_level" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Ta'lim</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">{{ getEducationLabel(application.worker.education_level) }}</span>
              </div>
              <div v-if="application.worker?.expected_salary_min || application.worker?.expected_salary_max" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Maosh kutish</span>
                <span class="font-medium text-surface-900 dark:text-surface-100">
                  {{ formatSalary(application.worker.expected_salary_min, application.worker.expected_salary_max) }}
                </span>
              </div>
              <div v-if="application.worker?.work_types?.length" class="flex justify-between text-sm">
                <span class="text-surface-500 dark:text-surface-400">Ish turi</span>
                <div class="flex flex-wrap gap-1 justify-end">
                  <AppBadge v-for="wt in application.worker.work_types" :key="wt" size="sm" variant="secondary">
                    {{ getWorkTypeLabel(wt) }}
                  </AppBadge>
                </div>
              </div>
              <div v-if="application.worker?.skills?.length" class="pt-2 border-t border-surface-100 dark:border-surface-800">
                <span class="text-sm text-surface-500 dark:text-surface-400 mb-2 block">Ko'nikmalar</span>
                <div class="flex flex-wrap gap-1.5">
                  <AppBadge v-for="skill in application.worker.skills" :key="skill" size="sm">
                    {{ skill }}
                  </AppBadge>
                </div>
              </div>
              <div v-if="application.worker?.bio" class="pt-2 border-t border-surface-100 dark:border-surface-800">
                <span class="text-sm text-surface-500 dark:text-surface-400 mb-1 block">O'zi haqida</span>
                <p class="text-sm text-surface-700 dark:text-surface-300 whitespace-pre-line">{{ application.worker.bio }}</p>
              </div>
              <!-- Telegram & Contact info -->
              <div v-if="application.worker?.user" class="pt-2 border-t border-surface-100 dark:border-surface-800">
                <div v-if="application.worker.user.username" class="flex justify-between text-sm">
                  <span class="text-surface-500 dark:text-surface-400">Telegram</span>
                  <a
                    :href="`https://t.me/${application.worker.user.username}`"
                    target="_blank"
                    class="font-medium text-brand-600 dark:text-brand-400 hover:underline"
                  >
                    @{{ application.worker.user.username }}
                  </a>
                </div>
                <div v-if="application.worker.user.phone" class="flex justify-between text-sm mt-2">
                  <span class="text-surface-500 dark:text-surface-400">Telefon</span>
                  <a
                    :href="`tel:${application.worker.user.phone}`"
                    class="font-medium text-brand-600 dark:text-brand-400 hover:underline"
                  >
                    {{ application.worker.user.phone }}
                  </a>
                </div>
                <div v-if="application.worker.user.last_active_at" class="flex justify-between text-sm mt-2">
                  <span class="text-surface-500 dark:text-surface-400">Oxirgi faollik</span>
                  <span class="text-surface-900 dark:text-surface-100">{{ formatRelativeDate(application.worker.user.last_active_at) }}</span>
                </div>
              </div>
              <!-- LinkedIn -->
              <div v-if="application.worker?.linkedin_url" class="pt-2 border-t border-surface-100 dark:border-surface-800">
                <div class="flex justify-between text-sm">
                  <span class="text-surface-500 dark:text-surface-400">LinkedIn</span>
                  <a
                    :href="application.worker.linkedin_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 font-medium text-brand-600 dark:text-brand-400 hover:underline"
                  >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    LinkedIn profili
                  </a>
                </div>
                <div v-if="application.worker.linkedin_imported_at" class="mt-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400">
                    LinkedIn dan import qilindi
                  </span>
                </div>
              </div>
            </div>
          </AppCard>

          <!-- Match Analysis -->
          <AppCard v-if="matchAnalysis">
            <template #header>
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                  Moslik tahlili
                </h3>
                <div class="flex items-center gap-2">
                  <span :class="[
                    'text-2xl font-bold',
                    matchAnalysis.overall_score >= 80 ? 'text-success-600 dark:text-success-400' :
                    matchAnalysis.overall_score >= 60 ? 'text-brand-600 dark:text-brand-400' :
                    matchAnalysis.overall_score >= 40 ? 'text-warning-600 dark:text-warning-400' :
                    'text-danger-600 dark:text-danger-400',
                  ]">
                    {{ matchAnalysis.overall_score }}%
                  </span>
                </div>
              </div>
            </template>

            <!-- Recommendation -->
            <div :class="[
              'mb-4 px-3 py-2 rounded-lg text-sm font-medium',
              matchAnalysis.overall_score >= 80 ? 'bg-success-50 dark:bg-success-950/30 text-success-700 dark:text-success-300' :
              matchAnalysis.overall_score >= 60 ? 'bg-brand-50 dark:bg-brand-950/30 text-brand-700 dark:text-brand-300' :
              matchAnalysis.overall_score >= 40 ? 'bg-warning-50 dark:bg-warning-950/30 text-warning-700 dark:text-warning-300' :
              'bg-danger-50 dark:bg-danger-950/30 text-danger-700 dark:text-danger-300',
            ]">
              {{ matchAnalysis.recommendation }}
            </div>

            <!-- Criteria -->
            <div class="space-y-3">
              <div
                v-for="c in matchAnalysis.criteria"
                :key="c.key"
                class="flex items-center gap-3"
              >
                <div :class="[
                  'w-6 h-6 rounded-full flex items-center justify-center shrink-0',
                  c.matched
                    ? 'bg-success-100 dark:bg-success-900/40 text-success-600 dark:text-success-400'
                    : 'bg-surface-100 dark:bg-surface-800 text-surface-400 dark:text-surface-500',
                ]">
                  <CheckCircleIcon v-if="c.matched" class="h-4 w-4" />
                  <span v-else class="text-xs font-bold">—</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ c.label }}</span>
                    <span class="text-xs text-surface-500 dark:text-surface-400">{{ c.score }}/{{ c.max }}</span>
                  </div>
                  <p class="text-xs text-surface-500 dark:text-surface-400 truncate">{{ c.detail }}</p>
                </div>
              </div>
            </div>
          </AppCard>

          <!-- Questionnaire Results -->
          <AppCard v-if="questionnaireResponse">
            <template #header>
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                  Savolnoma natijalari
                </h3>
                <div class="flex items-center gap-3">
                  <AppBadge
                    :variant="questionnaireResponse.knockout_passed ? 'success' : 'danger'"
                    size="sm"
                  >
                    {{ questionnaireResponse.knockout_passed ? 'Knockout o\'tdi' : 'Knockout yiqildi' }}
                  </AppBadge>
                  <span v-if="questionnaireResponse.time_spent_seconds" class="text-sm text-surface-500 dark:text-surface-400">
                    {{ formatTimeSpent(questionnaireResponse.time_spent_seconds) }}
                  </span>
                </div>
              </div>
            </template>

            <!-- Score progress bar -->
            <div class="mb-6">
              <AppProgressBar
                :value="questionnaireResponse.total_score || 0"
                :max="100"
                :color="getScoreColor(questionnaireResponse.total_score, questionnaireResponse.knockout_passed)"
                label="Umumiy ball"
                size="lg"
              />
            </div>

            <!-- Individual questions -->
            <div class="space-y-4">
              <div
                v-for="(answer, index) in sortedAnswers"
                :key="answer.id"
                :class="[
                  'p-4 rounded-lg border',
                  answer.is_knockout_failed
                    ? 'border-danger-200 dark:border-danger-800 bg-danger-50/50 dark:bg-danger-950/20'
                    : 'border-surface-200 dark:border-surface-800',
                ]"
              >
                <!-- Question header -->
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span class="text-sm font-medium text-surface-500 dark:text-surface-400">
                        Savol {{ index + 1 }}
                      </span>
                      <AppBadge size="sm">
                        {{ getQuestionTypeLabel(answer.question?.type) }}
                      </AppBadge>
                      <AppBadge v-if="answer.question?.is_knockout" size="sm" variant="danger">
                        Knockout
                      </AppBadge>
                    </div>
                    <p class="font-medium text-surface-900 dark:text-surface-100">
                      {{ answer.question?.text_uz }}
                    </p>
                    <p v-if="answer.question?.text_ru" class="text-sm text-surface-500 dark:text-surface-400 mt-0.5">
                      {{ answer.question.text_ru }}
                    </p>
                  </div>
                  <div class="text-right ml-4 shrink-0">
                    <p class="text-xs text-surface-500 dark:text-surface-400">Og'irlik</p>
                    <p class="font-semibold text-surface-900 dark:text-surface-100">{{ answer.question?.weight }}</p>
                  </div>
                </div>

                <!-- Answer display per type -->
                <div class="mt-3">
                  <!-- Choice types: single_choice / multi_select / knockout -->
                  <div v-if="isChoiceType(answer.question?.type) && answer.question?.options?.length" class="space-y-1.5">
                    <div
                      v-for="option in answer.question.options"
                      :key="option.id"
                      :class="[
                        'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm',
                        isOptionSelected(answer, option)
                          ? (option.is_correct
                            ? 'bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300 font-medium'
                            : 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 font-medium')
                          : (option.is_correct
                            ? 'bg-success-50 dark:bg-success-950/10 text-success-600 dark:text-success-400'
                            : 'text-surface-600 dark:text-surface-400'),
                      ]"
                    >
                      <CheckCircleIcon v-if="isOptionSelected(answer, option)" class="h-4 w-4 shrink-0" />
                      <div v-else class="w-4 h-4 rounded-full border-2 border-surface-300 dark:border-surface-600 shrink-0" />
                      <span class="flex-1">{{ option.label_uz }}</span>
                      <span v-if="option.score_value" class="text-xs opacity-75">({{ option.score_value }} ball)</span>
                    </div>
                  </div>

                  <!-- Open text -->
                  <div v-else-if="answer.question?.type === 'open_text'">
                    <div class="p-3 bg-surface-100 dark:bg-surface-800 rounded-lg text-sm text-surface-700 dark:text-surface-300 whitespace-pre-line">
                      {{ getAnswerText(answer) || 'Javob berilmagan' }}
                    </div>
                    <!-- Manual scoring -->
                    <div class="mt-3 flex items-center gap-3">
                      <input
                        v-model.number="manualScores[answer.id]"
                        type="number"
                        min="0"
                        max="100"
                        placeholder="0-100"
                        class="w-24 px-3 py-1.5 text-sm rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent text-center"
                      />
                      <AppButton
                        size="sm"
                        variant="outline"
                        :loading="savingManualScore === answer.id"
                        @click="saveManualScore(answer)"
                      >
                        Baholash
                      </AppButton>
                      <span v-if="answer.manual_score !== null" class="text-sm text-success-600 dark:text-success-400">
                        Qo'lda baho: {{ answer.manual_score }}
                      </span>
                    </div>
                  </div>

                  <!-- Number range -->
                  <div v-else-if="answer.question?.type === 'number_range'">
                    <div class="inline-flex items-center gap-2 px-3 py-2 bg-surface-100 dark:bg-surface-800 rounded-lg">
                      <span class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                        {{ getAnswerValue(answer) ?? 'Javob berilmagan' }}
                      </span>
                    </div>
                    <p v-if="answer.question?.correct_answer" class="text-xs text-surface-500 dark:text-surface-400 mt-1">
                      To'g'ri oraliq: {{ answer.question.correct_answer.min }} — {{ answer.question.correct_answer.max }}
                    </p>
                  </div>

                  <!-- File upload -->
                  <div v-else-if="answer.question?.type === 'file_upload'">
                    <div class="flex items-center gap-2 p-3 bg-surface-100 dark:bg-surface-800 rounded-lg">
                      <PaperClipIcon class="h-5 w-5 text-surface-400" />
                      <span class="text-sm text-surface-700 dark:text-surface-300">
                        {{ answer.answer_value?.filename || 'Fayl yuklangan' }}
                      </span>
                    </div>
                  </div>

                  <!-- Generic fallback -->
                  <div v-else>
                    <div class="p-3 bg-surface-100 dark:bg-surface-800 rounded-lg text-sm text-surface-700 dark:text-surface-300">
                      {{ getAnswerText(answer) || 'Javob berilmagan' }}
                    </div>
                  </div>
                </div>

                <!-- Score per question -->
                <div class="mt-3 flex items-center justify-between pt-3 border-t border-surface-200 dark:border-surface-700">
                  <span class="text-sm text-surface-500 dark:text-surface-400">Ball</span>
                  <div class="flex items-center gap-2">
                    <span :class="[
                      'font-semibold',
                      answer.is_knockout_failed ? 'text-danger-600 dark:text-danger-400' : 'text-surface-900 dark:text-surface-100',
                    ]">
                      {{ answer.manual_score !== null ? answer.manual_score : (answer.score ?? '—') }}
                    </span>
                    <AppBadge v-if="answer.is_knockout_failed" size="sm" variant="danger">
                      Yiqildi
                    </AppBadge>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty state -->
            <div v-if="!sortedAnswers.length" class="text-center py-8 text-surface-500 dark:text-surface-400">
              Javoblar topilmadi
            </div>
          </AppCard>

          <!-- Questionnaire exists but no response (candidate didn't answer) -->
          <AppCard v-else-if="questionnaire && questionnaire.questions?.length">
            <template #header>
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
                  Savolnoma savollari
                </h3>
                <AppBadge variant="warning" size="sm">
                  Javob berilmagan
                </AppBadge>
              </div>
            </template>

            <div class="mb-4 p-3 bg-surface-100 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-lg">
              <p class="text-sm text-surface-600 dark:text-surface-400">
                Nomzod savolnomani yakunlagan, ammo savollarga javob bermagan.
              </p>
            </div>

            <div class="space-y-4">
              <div
                v-for="(question, index) in questionnaire.questions"
                :key="question.id"
                class="p-4 rounded-lg border border-surface-200 dark:border-surface-800"
              >
                <!-- Question header -->
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span class="text-sm font-medium text-surface-500 dark:text-surface-400">
                        Savol {{ index + 1 }}
                      </span>
                      <AppBadge size="sm">
                        {{ getQuestionTypeLabel(question.type) }}
                      </AppBadge>
                      <AppBadge v-if="question.is_knockout" size="sm" variant="danger">
                        Knockout
                      </AppBadge>
                    </div>
                    <p class="font-medium text-surface-900 dark:text-surface-100">
                      {{ question.text_uz }}
                    </p>
                    <p v-if="question.text_ru" class="text-sm text-surface-500 dark:text-surface-400 mt-0.5">
                      {{ question.text_ru }}
                    </p>
                  </div>
                  <div v-if="question.weight" class="text-right ml-4 shrink-0">
                    <p class="text-xs text-surface-500 dark:text-surface-400">Og'irlik</p>
                    <p class="font-semibold text-surface-900 dark:text-surface-100">{{ question.weight }}</p>
                  </div>
                </div>

                <!-- Show options for choice questions (no answer selected) -->
                <div v-if="isChoiceType(question.type) && question.options?.length" class="space-y-1.5">
                  <div
                    v-for="option in question.options"
                    :key="option.id"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-surface-500 dark:text-surface-400"
                  >
                    <div class="w-4 h-4 rounded-full border-2 border-surface-300 dark:border-surface-600 shrink-0" />
                    <span class="flex-1">{{ option.label_uz }}</span>
                  </div>
                </div>

                <!-- Other types — just show "not answered" -->
                <div v-else class="p-3 bg-surface-100 dark:bg-surface-800 rounded-lg text-sm text-surface-400 dark:text-surface-500 italic">
                  Javob berilmagan
                </div>
              </div>
            </div>
          </AppCard>

          <!-- No questionnaire at all -->
          <AppCard v-else-if="!questionnaire">
            <div class="text-center py-8">
              <ClipboardDocumentListIcon class="h-12 w-12 mx-auto text-surface-400 dark:text-surface-500 mb-3" />
              <p class="text-surface-600 dark:text-surface-400">Bu vakansiya uchun savolnoma yaratilmagan</p>
            </div>
          </AppCard>

          <!-- Cover Letter -->
          <AppCard v-if="application.cover_letter">
            <template #header>
              <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Xat</h3>
            </template>
            <p class="text-sm text-surface-700 dark:text-surface-300 whitespace-pre-line leading-relaxed">
              {{ application.cover_letter }}
            </p>
          </AppCard>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
          <!-- Stage Management -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Bosqich</h3>
            </template>

            <!-- Withdrawn banner -->
            <div v-if="isWithdrawn" class="mb-3 p-3 rounded-lg bg-surface-100 dark:bg-surface-800 border border-surface-200 dark:border-surface-700">
              <div class="flex items-center gap-2 text-surface-600 dark:text-surface-400">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span class="text-sm font-medium">Nomzod arizasini bekor qilgan</span>
              </div>
            </div>

            <div class="space-y-1.5">
              <button
                v-for="stage in pipelineStages.filter(s => s.key !== 'withdrawn')"
                :key="stage.key"
                :class="[
                  'w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                  application.stage === stage.key
                    ? 'bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300'
                    : isWithdrawn
                      ? 'text-surface-400 dark:text-surface-600 cursor-not-allowed'
                      : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800',
                ]"
                :disabled="changingStage || isWithdrawn"
                @click="changeStage(stage.key)"
              >
                {{ stage.label }}
              </button>
            </div>
          </AppCard>

          <!-- Rating -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Baho</h3>
            </template>
            <div class="flex items-center gap-1">
              <button
                v-for="star in 5"
                :key="star"
                class="p-1 transition-transform hover:scale-110"
                @click="setRating(star)"
              >
                <StarIconSolid
                  v-if="star <= (application.recruiter_rating || 0)"
                  class="h-7 w-7 text-warning-400"
                />
                <StarIcon
                  v-else
                  class="h-7 w-7 text-surface-300 dark:text-surface-600"
                />
              </button>
            </div>
          </AppCard>

          <!-- Notes -->
          <AppCard>
            <template #header>
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Eslatmalar</h3>
                <span class="text-xs text-surface-400">{{ application.notes?.length || 0 }}</span>
              </div>
            </template>

            <div v-if="application.notes?.length" class="space-y-3 mb-4">
              <div
                v-for="note in application.notes"
                :key="note.id"
                class="p-3 bg-surface-50 dark:bg-surface-800 rounded-lg"
              >
                <p class="text-sm text-surface-700 dark:text-surface-300 whitespace-pre-line">{{ note.note }}</p>
                <p class="text-xs text-surface-400 mt-1.5">
                  {{ note.user?.first_name }} &bull; {{ formatRelativeDate(note.created_at) }}
                </p>
              </div>
            </div>

            <div>
              <textarea
                v-model="newNote"
                rows="2"
                placeholder="Eslatma qo'shish..."
                class="w-full px-3 py-2 text-sm rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
              />
              <AppButton
                class="mt-2"
                size="sm"
                variant="outline"
                full-width
                :loading="savingNote"
                :disabled="!newNote.trim()"
                @click="addNote"
              >
                Qo'shish
              </AppButton>
            </div>
          </AppCard>

          <!-- Tags -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Teglar</h3>
            </template>

            <div v-if="application.tags?.length" class="flex flex-wrap gap-1.5 mb-3">
              <span
                v-for="tag in application.tags"
                :key="tag.id"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                :style="{ backgroundColor: tag.color + '20', color: tag.color }"
              >
                {{ tag.name }}
              </span>
            </div>

            <div class="flex gap-2">
              <input
                v-model="newTag"
                type="text"
                placeholder="Teg nomi..."
                class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-800 text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                @keyup.enter="addTag"
              />
              <AppButton size="sm" variant="outline" :disabled="!newTag.trim()" @click="addTag">
                +
              </AppButton>
            </div>
          </AppCard>

          <!-- Application Meta -->
          <AppCard>
            <template #header>
              <h3 class="text-sm font-semibold text-surface-900 dark:text-surface-100">Ma'lumotlar</h3>
            </template>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-surface-500 dark:text-surface-400">Ariza sanasi</span>
                <span class="text-surface-900 dark:text-surface-100">{{ formatDate(application.created_at) }}</span>
              </div>
              <div v-if="application.viewed_at" class="flex justify-between">
                <span class="text-surface-500 dark:text-surface-400">Ko'rilgan</span>
                <span class="text-surface-900 dark:text-surface-100">{{ formatDate(application.viewed_at) }}</span>
              </div>
              <div v-if="application.source" class="flex justify-between">
                <span class="text-surface-500 dark:text-surface-400">Manba</span>
                <span class="text-surface-900 dark:text-surface-100">{{ application.source }}</span>
              </div>
              <div v-if="application.matching_score" class="flex justify-between">
                <span class="text-surface-500 dark:text-surface-400">Moslik balli</span>
                <span class="text-surface-900 dark:text-surface-100">{{ application.matching_score }}%</span>
              </div>
            </div>
          </AppCard>
        </div>
      </div>
    </div>

    <!-- Reject Reason Dialog -->
    <AppConfirmDialog
      :open="showRejectDialog"
      type="danger"
      title="Arizani rad etish"
      message="Rad etish sababini kiriting (ixtiyoriy)"
      confirm-text="Rad etish"
      cancel-text="Bekor qilish"
      :loading="changingStage"
      @confirm="confirmReject"
      @cancel="showRejectDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { formatLocation } from '@/shared/formatters';
import { toast } from 'vue-sonner';
import {
  ArrowLeftIcon,
  StarIcon,
  PaperClipIcon,
  CheckCircleIcon,
  ClipboardDocumentListIcon,
  DocumentArrowDownIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';
import AppCard from '../../components/ui/AppCard.vue';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppProgressBar from '../../components/ui/AppProgressBar.vue';
import AppLoadingSpinner from '../../components/ui/AppLoadingSpinner.vue';
import AppConfirmDialog from '../../components/ui/AppConfirmDialog.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const application = ref(null);
const questionnaire = ref(null);
const matchAnalysis = ref(null);

// Notes
const newNote = ref('');
const savingNote = ref(false);

// Tags
const newTag = ref('');

// Stage
const changingStage = ref(false);
const showRejectDialog = ref(false);
const rejectReason = ref('');

// Manual scoring
const manualScores = reactive({});
const savingManualScore = ref(null);

// Pipeline stages
const pipelineStages = [
  { key: 'new', label: 'Yangi' },
  { key: 'reviewed', label: 'Ko\'rilgan' },
  { key: 'shortlisted', label: 'Tanlangan' },
  { key: 'interview', label: 'Intervyu' },
  { key: 'offered', label: 'Taklif' },
  { key: 'hired', label: 'Qabul qilindi' },
  { key: 'rejected', label: 'Rad etildi' },
  { key: 'withdrawn', label: 'Bekor qilingan' },
];

const isWithdrawn = computed(() => application.value?.stage === 'withdrawn');

// Question type labels
const questionTypeLabels = {
  single_choice: 'Bir tanlov',
  multi_select: 'Ko\'p tanlov',
  number_range: 'Raqam',
  open_text: 'Ochiq javob',
  knockout: 'Knockout',
  file_upload: 'Fayl',
  text: 'Matn',
  multiple_choice: 'Ko\'p tanlov',
  rating: 'Baho',
  yes_no: 'Ha/Yo\'q',
};

// Computed
const applicantName = computed(() => {
  const w = application.value?.worker;
  if (w?.full_name) return w.full_name;
  const u = w?.user;
  if (u?.first_name || u?.last_name) return `${u.first_name || ''} ${u.last_name || ''}`.trim();
  return 'Nomzod';
});

const applicantInitials = computed(() => {
  const name = applicantName.value;
  const parts = name.split(' ').filter(Boolean);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.slice(0, 2).toUpperCase();
});

const questionnaireResponse = computed(() => application.value?.questionnaire_response);

const sortedAnswers = computed(() => {
  const answers = questionnaireResponse.value?.answers || [];
  return [...answers].sort((a, b) => {
    const orderA = a.question?.sort_order ?? 0;
    const orderB = b.question?.sort_order ?? 0;
    return orderA - orderB;
  });
});

// Load data
onMounted(loadApplication);

async function loadApplication() {
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/recruiter/applications/${route.params.applicationId}`);
    application.value = data.application;
    questionnaire.value = data.questionnaire || null;
    matchAnalysis.value = data.match_analysis || null;

    // Pre-populate manual scores
    if (application.value?.questionnaire_response?.answers) {
      for (const answer of application.value.questionnaire_response.answers) {
        if (answer.manual_score !== null) {
          manualScores[answer.id] = answer.manual_score;
        }
      }
    }
  } catch (error) {
    application.value = null;
    if (error.response?.status !== 404) {
      toast.error('Arizani yuklashda xatolik');
    }
  } finally {
    loading.value = false;
  }
}

// Stage management
function changeStage(stage) {
  if (application.value.stage === stage) return;
  if (stage === 'rejected') {
    rejectReason.value = '';
    showRejectDialog.value = true;
    return;
  }
  doChangeStage(stage);
}

async function confirmReject() {
  await doChangeStage('rejected', rejectReason.value);
  showRejectDialog.value = false;
}

async function doChangeStage(stage, rejectedReason = null) {
  changingStage.value = true;
  try {
    const payload = { stage };
    if (rejectedReason) payload.rejected_reason = rejectedReason;

    const { data } = await axios.put(`/api/recruiter/applications/${application.value.id}/stage`, payload);
    application.value = { ...application.value, ...data.application };
    toast.success(`Bosqich o'zgartirildi: ${getStageLabel(stage)}`);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Xatolik yuz berdi');
  } finally {
    changingStage.value = false;
  }
}

// Rating
async function setRating(star) {
  try {
    const { data } = await axios.put(`/api/recruiter/applications/${application.value.id}/rate`, { rating: star });
    application.value = { ...application.value, ...data.application };
  } catch {
    toast.error('Baho berishda xatolik');
  }
}

// Notes
async function addNote() {
  if (!newNote.value.trim()) return;
  savingNote.value = true;
  try {
    const { data } = await axios.post(`/api/recruiter/applications/${application.value.id}/note`, {
      note: newNote.value,
    });
    if (!application.value.notes) application.value.notes = [];
    application.value.notes.unshift(data.note);
    newNote.value = '';
    toast.success('Eslatma qo\'shildi');
  } catch {
    toast.error('Eslatma qo\'shishda xatolik');
  } finally {
    savingNote.value = false;
  }
}

// Tags
async function addTag() {
  if (!newTag.value.trim()) return;
  try {
    const { data } = await axios.post(`/api/recruiter/applications/${application.value.id}/tags`, {
      tags: [newTag.value.trim()],
    });
    // Reload tags from response
    if (data.tags) {
      application.value.tags = data.tags.map(ct => ct.tag).filter(Boolean);
    }
    newTag.value = '';
    toast.success('Teg qo\'shildi');
  } catch {
    toast.error('Teg qo\'shishda xatolik');
  }
}

// Manual scoring
async function saveManualScore(answer) {
  const score = manualScores[answer.id];
  if (score === undefined || score === null || score === '') return;

  savingManualScore.value = answer.id;
  try {
    const { data } = await axios.put(`/api/recruiter/answers/${answer.id}/manual-score`, {
      score: parseInt(score),
    });
    // Update answer in-place
    answer.manual_score = data.answer.manual_score;
    // Update total score
    if (questionnaireResponse.value && data.new_total_score !== undefined) {
      questionnaireResponse.value.total_score = data.new_total_score;
    }
    toast.success('Baho saqlandi');
  } catch {
    toast.error('Baho saqlashda xatolik');
  } finally {
    savingManualScore.value = null;
  }
}

// Helpers
function isChoiceType(type) {
  return ['single_choice', 'multi_select', 'knockout', 'multiple_choice'].includes(type);
}

function isOptionSelected(answer, option) {
  const val = answer.answer_value;
  if (!val) return false;
  if (Array.isArray(val)) {
    return val.includes(option.id) || val.includes(option.value);
  }
  if (typeof val === 'object' && val.value) {
    return val.value === option.id || val.value === option.value;
  }
  return val === option.id || val === option.value;
}

function getAnswerText(answer) {
  const val = answer.answer_value;
  if (!val) return null;
  if (typeof val === 'string') return val;
  if (val.text) return val.text;
  if (val.value) return String(val.value);
  return JSON.stringify(val);
}

function getAnswerValue(answer) {
  const val = answer.answer_value;
  if (!val) return null;
  if (typeof val === 'number') return val;
  if (val.value !== undefined) return val.value;
  return val;
}

function getScoreColor(score, knockoutPassed) {
  if (!knockoutPassed) return 'danger';
  if (score >= 80) return 'success';
  if (score >= 50) return 'warning';
  return 'danger';
}

function getStageVariant(stage) {
  const stageStr = typeof stage === 'object' ? stage?.value || stage : stage;
  const map = {
    new: 'info',
    reviewed: 'secondary',
    shortlisted: 'warning',
    interview: 'primary',
    offered: 'success',
    hired: 'success',
    rejected: 'danger',
    withdrawn: 'default',
  };
  return map[stageStr] || 'secondary';
}

function getStageLabel(stage) {
  const stageStr = typeof stage === 'object' ? stage?.value || stage : stage;
  const s = pipelineStages.find(s => s.key === stageStr);
  return s ? s.label : stageStr;
}

function getQuestionTypeLabel(type) {
  return questionTypeLabels[type] || type;
}

function formatSalary(min, max) {
  const fmt = (n) => n ? Number(n).toLocaleString('uz-UZ') : null;
  const fMin = fmt(min);
  const fMax = fmt(max);
  if (fMin && fMax) return `${fMin} — ${fMax} so'm`;
  if (fMin) return `${fMin} so'm dan`;
  if (fMax) return `${fMax} so'm gacha`;
  return '—';
}

function formatDate(date) {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

function formatRelativeDate(date) {
  if (!date) return '';
  const now = new Date();
  const d = new Date(date);
  const diff = Math.floor((now - d) / 1000);
  if (diff < 60) return 'Hozirgina';
  if (diff < 3600) return `${Math.floor(diff / 60)} min oldin`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} soat oldin`;
  if (diff < 604800) return `${Math.floor(diff / 86400)} kun oldin`;
  return formatDate(date);
}

function formatTimeSpent(seconds) {
  if (!seconds) return '';
  const min = Math.floor(seconds / 60);
  const sec = seconds % 60;
  if (min > 0) return `${min} daqiqa ${sec} soniya`;
  return `${sec} soniya`;
}

function getAge(birthDate) {
  if (!birthDate) return null;
  const today = new Date();
  const birth = new Date(birthDate);
  let age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--;
  }
  return age;
}

function getEducationLabel(level) {
  const labels = {
    secondary: 'O\'rta',
    vocational: 'O\'rta maxsus',
    bachelor: 'Bakalavr',
    master: 'Magistr',
    phd: 'PhD',
    none: 'Ko\'rsatilmagan',
  };
  return labels[level] || level;
}

function getWorkTypeLabel(type) {
  const labels = {
    full_time: 'To\'liq stavka',
    part_time: 'Yarim stavka',
    remote: 'Masofadan',
    freelance: 'Frilanser',
    internship: 'Amaliyot',
    contract: 'Shartnoma',
  };
  return labels[type] || type;
}
</script>
