<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-500 via-brand-600 to-info-600 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Telegram orqali ishga joylashish platformasi
          </h1>
          <p class="text-xl md:text-2xl text-brand-100 mb-8">
            O'zbekistonning birinchi Telegram asosidagi rekruting platformasi
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link
              to="/auth/register"
              class="px-8 py-4 bg-white text-brand-600 rounded-xl font-semibold hover:bg-brand-50 transition-colors text-lg"
            >
              Bepul boshlash
            </router-link>
            <router-link
              to="/auth/login"
              class="px-8 py-4 bg-brand-700 text-white rounded-xl font-semibold hover:bg-brand-800 transition-colors text-lg"
            >
              Kirish
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12 bg-surface-50 dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600 dark:text-brand-400">25M+</div>
            <div class="text-surface-600 dark:text-surface-400 mt-2">Telegram foydalanuvchi</div>
          </div>
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600 dark:text-brand-400">14</div>
            <div class="text-surface-600 dark:text-surface-400 mt-2">Viloyat qamrovi</div>
          </div>
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600 dark:text-brand-400">35K+</div>
            <div class="text-surface-600 dark:text-surface-400 mt-2">dan boshlab</div>
          </div>
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600 dark:text-brand-400">10s</div>
            <div class="text-surface-600 dark:text-surface-400 mt-2">tez to'lov</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white dark:bg-surface-950">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-surface-100 mb-4">
            Xususiyatlar
          </h2>
          <p class="text-lg text-surface-600 dark:text-surface-400 max-w-2xl mx-auto">
            Rekruting jarayonini soddalashtiruvchi zamonaviy vositalar
          </p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-brand-100 dark:bg-brand-900 flex items-center justify-center mb-4">
              📝
            </div>
            <h3 class="text-xl font-semibold mb-2 text-surface-900 dark:text-surface-100">Savolnoma tizimi</h3>
            <p class="text-surface-600 dark:text-surface-400">
              100 ta arizadan 10 tasini 30 sekundda tanlang
            </p>
          </div>
          <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-brand-100 dark:bg-brand-900 flex items-center justify-center mb-4">
              📊
            </div>
            <h3 class="text-xl font-semibold mb-2 text-surface-900 dark:text-surface-100">Kanban Pipeline</h3>
            <p class="text-surface-600 dark:text-surface-400">
              Drag-and-drop bilan kandidatlar boshqaruvi
            </p>
          </div>
          <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-brand-100 dark:bg-brand-900 flex items-center justify-center mb-4">
              💰
            </div>
            <h3 class="text-xl font-semibold mb-2 text-surface-900 dark:text-surface-100">Tez to'lov</h3>
            <p class="text-surface-600 dark:text-surface-400">
              Payme/Click orqali 10 sekundda to'lang
            </p>
          </div>
          <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-success-100 dark:bg-success-900 flex items-center justify-center mb-4">
              🎯
            </div>
            <h3 class="text-xl font-semibold mb-2 text-surface-900 dark:text-surface-100">Talent Pool</h3>
            <p class="text-surface-600 dark:text-surface-400">
              Keyingi vakansiyalar uchun kandidatlar bazasi
            </p>
          </div>
          <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-info-100 dark:bg-info-900 flex items-center justify-center mb-4">
              📱
            </div>
            <h3 class="text-xl font-semibold mb-2 text-surface-900 dark:text-surface-100">Telegram integratsiya</h3>
            <p class="text-surface-600 dark:text-surface-400">
              Bot orqali avtomatik bildirishnomalar
            </p>
          </div>
          <div class="p-6 bg-surface-50 dark:bg-surface-900 rounded-2xl hover:shadow-lg transition-shadow">
            <div class="w-12 h-12 rounded-lg bg-warning-100 dark:bg-warning-900 flex items-center justify-center mb-4">
              📈
            </div>
            <h3 class="text-xl font-semibold mb-2 text-surface-900 dark:text-surface-100">Analitika</h3>
            <p class="text-surface-600 dark:text-surface-400">
              Real-time rekruting metrikalar va hisobotlar
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 bg-surface-50 dark:bg-surface-900">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-surface-100 mb-4">
            Narxlar
          </h2>
          <p class="text-lg text-surface-600 dark:text-surface-400 max-w-2xl mx-auto">
            Sizning biznesingiz uchun qulay tarifni tanlang
          </p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <!-- Starter Plan -->
          <div class="bg-white dark:bg-surface-800 rounded-2xl p-8 border border-surface-200 dark:border-surface-700">
            <h3 class="text-2xl font-bold text-surface-900 dark:text-surface-100 mb-2">Starter</h3>
            <p class="text-surface-600 dark:text-surface-400 mb-6">Kichik bizneslar uchun</p>
            <div class="mb-6">
              <span class="text-4xl font-bold text-surface-900 dark:text-surface-100">35,000</span>
              <span class="text-surface-600 dark:text-surface-400"> so'm/ariza</span>
            </div>
            <ul class="space-y-3 mb-8">
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">1 ta faol vakansiya</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Savolnoma tizimi</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Kanban pipeline</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Email qo'llab-quvvatlash</span>
              </li>
            </ul>
            <AppButton variant="outline" size="lg" full-width>
              Boshlash
            </AppButton>
          </div>

          <!-- Professional Plan (Popular) -->
          <div class="bg-white dark:bg-surface-800 rounded-2xl p-8 border-2 border-brand-500 relative">
            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
              <AppBadge variant="primary">Mashhur</AppBadge>
            </div>
            <h3 class="text-2xl font-bold text-surface-900 dark:text-surface-100 mb-2">Professional</h3>
            <p class="text-surface-600 dark:text-surface-400 mb-6">O'sib borayotgan kompaniyalar uchun</p>
            <div class="mb-6">
              <span class="text-4xl font-bold text-surface-900 dark:text-surface-100">25,000</span>
              <span class="text-surface-600 dark:text-surface-400"> so'm/ariza</span>
            </div>
            <ul class="space-y-3 mb-8">
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">5 tagacha faol vakansiya</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Barcha Starter xususiyatlar</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Talent Pool</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Analitika va hisobotlar</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Telegram reklama</span>
              </li>
            </ul>
            <AppButton variant="primary" size="lg" full-width>
              Boshlash
            </AppButton>
          </div>

          <!-- Enterprise Plan -->
          <div class="bg-white dark:bg-surface-800 rounded-2xl p-8 border border-surface-200 dark:border-surface-700">
            <h3 class="text-2xl font-bold text-surface-900 dark:text-surface-100 mb-2">Enterprise</h3>
            <p class="text-surface-600 dark:text-surface-400 mb-6">Yirik kompaniyalar uchun</p>
            <div class="mb-6">
              <span class="text-4xl font-bold text-surface-900 dark:text-surface-100">Maxsus</span>
            </div>
            <ul class="space-y-3 mb-8">
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Cheksiz vakansiyalar</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Barcha Professional xususiyatlar</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Jamoa boshqaruvi</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">API access</span>
              </li>
              <li class="flex items-start gap-2">
                <CheckIcon class="h-5 w-5 text-success-600 flex-shrink-0 mt-0.5" />
                <span class="text-surface-700 dark:text-surface-300">Shaxsiy menejer</span>
              </li>
            </ul>
            <AppButton variant="outline" size="lg" full-width>
              Bog'lanish
            </AppButton>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-white dark:bg-surface-950">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-surface-100 mb-4">
            Mijozlar fikri
          </h2>
          <p class="text-lg text-surface-600 dark:text-surface-400 max-w-2xl mx-auto">
            IshTop yordamida o'z jamoalarini to'ldirayotgan kompaniyalar
          </p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8">
            <div class="flex items-center gap-1 mb-4">
              <StarIcon v-for="i in 5" :key="i" class="h-5 w-5 text-warning-500 fill-current" />
            </div>
            <p class="text-surface-700 dark:text-surface-300 mb-6">
              "IshTop bizga oyiga 50 tagacha yangi xodimni topishda yordam berdi. Jarayon ancha tezlashdi va sifat ham yaxshi."
            </p>
            <div class="flex items-center gap-3">
              <AppAvatar name="Aziz Karimov" color="brand" />
              <div>
                <p class="font-semibold text-surface-900 dark:text-surface-100">Aziz Karimov</p>
                <p class="text-sm text-surface-600 dark:text-surface-400">Grand Hotel, HR Director</p>
              </div>
            </div>
          </div>

          <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8">
            <div class="flex items-center gap-1 mb-4">
              <StarIcon v-for="i in 5" :key="i" class="h-5 w-5 text-warning-500 fill-current" />
            </div>
            <p class="text-surface-700 dark:text-surface-300 mb-6">
              "Savolnoma tizimi juda qulaylik yaratdi. 100 ta arizani 5 daqiqada ko'rib chiqamiz va eng yaxshilarini tanlaymiz."
            </p>
            <div class="flex items-center gap-3">
              <AppAvatar name="Madina Alieva" color="success" />
              <div>
                <p class="font-semibold text-surface-900 dark:text-surface-100">Madina Alieva</p>
                <p class="text-sm text-surface-600 dark:text-surface-400">TechCorp, Recruiter</p>
              </div>
            </div>
          </div>

          <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8">
            <div class="flex items-center gap-1 mb-4">
              <StarIcon v-for="i in 5" :key="i" class="h-5 w-5 text-warning-500 fill-current" />
            </div>
            <p class="text-surface-700 dark:text-surface-300 mb-6">
              "Telegram integratsiyasi ajoyib! Arizalar darhol keladi va kandidatlar bilan tez bog'lanishimiz mumkin."
            </p>
            <div class="flex items-center gap-3">
              <AppAvatar name="Jahongir Rahimov" color="info" />
              <div>
                <p class="font-semibold text-surface-900 dark:text-surface-100">Jahongir Rahimov</p>
                <p class="text-sm text-surface-600 dark:text-surface-400">FoodChain, CEO</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-surface-50 dark:bg-surface-900">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-surface-100 mb-4">
            Tez-tez so'raladigan savollar
          </h2>
        </div>
        <div class="space-y-4">
          <div
            v-for="(faq, index) in faqs"
            :key="index"
            class="bg-white dark:bg-surface-800 rounded-lg overflow-hidden"
          >
            <button
              class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors"
              @click="toggleFaq(index)"
            >
              <span class="font-semibold text-surface-900 dark:text-surface-100">{{ faq.question }}</span>
              <ChevronDownIcon
                :class="[
                  'h-5 w-5 text-surface-500 transition-transform',
                  openFaqIndex === index ? 'rotate-180' : '',
                ]"
              />
            </button>
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 max-h-0"
              enter-to-class="opacity-100 max-h-96"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="opacity-100 max-h-96"
              leave-to-class="opacity-0 max-h-0"
            >
              <div v-if="openFaqIndex === index" class="px-6 pb-4">
                <p class="text-surface-600 dark:text-surface-400">{{ faq.answer }}</p>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-brand-500 via-brand-600 to-info-600 text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">
          Bugun boshlab ko'ring
        </h2>
        <p class="text-xl text-brand-100 mb-8">
          Bir necha daqiqada ro'yxatdan o'ting va birinchi vakansiyangizni e'lon qiling
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/auth/register"
            class="px-8 py-4 bg-white text-brand-600 rounded-xl font-semibold hover:bg-brand-50 transition-colors text-lg"
          >
            Bepul sinab ko'rish
          </router-link>
          <a
            href="#pricing"
            class="px-8 py-4 bg-brand-700 text-white rounded-xl font-semibold hover:bg-brand-800 transition-colors text-lg border-2 border-white/20"
          >
            Narxlarni ko'rish
          </a>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-surface-950 text-surface-300 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <div>
            <div class="flex items-center gap-2 mb-4">
              <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-lg">
                I
              </div>
              <span class="text-xl font-bold text-white">IshTop</span>
            </div>
            <p class="text-sm">
              Telegram orqali ishga joylashish platformasi
            </p>
          </div>

          <div>
            <h3 class="font-semibold text-white mb-4">Platformada</h3>
            <ul class="space-y-2 text-sm">
              <li><a href="#features" class="hover:text-brand-400 transition-colors">Xususiyatlar</a></li>
              <li><a href="#pricing" class="hover:text-brand-400 transition-colors">Narxlar</a></li>
              <li><a href="#testimonials" class="hover:text-brand-400 transition-colors">Mijozlar fikri</a></li>
              <li><a href="#faq" class="hover:text-brand-400 transition-colors">FAQ</a></li>
            </ul>
          </div>

          <div>
            <h3 class="font-semibold text-white mb-4">Kompaniya</h3>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-brand-400 transition-colors">Biz haqimizda</a></li>
              <li><a href="#" class="hover:text-brand-400 transition-colors">Blog</a></li>
              <li><a href="#" class="hover:text-brand-400 transition-colors">Kontaktlar</a></li>
              <li><a href="#" class="hover:text-brand-400 transition-colors">Yordam</a></li>
            </ul>
          </div>

          <div>
            <h3 class="font-semibold text-white mb-4">Huquqiy</h3>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-brand-400 transition-colors">Foydalanish shartlari</a></li>
              <li><a href="#" class="hover:text-brand-400 transition-colors">Maxfiylik siyosati</a></li>
              <li><a href="#" class="hover:text-brand-400 transition-colors">Cookie siyosati</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-surface-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-sm">
            © 2024 IshTop. Barcha huquqlar himoyalangan.
          </p>
          <div class="flex gap-4">
            <a href="#" class="hover:text-brand-400 transition-colors">
              <span class="sr-only">Telegram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z"/>
              </svg>
            </a>
            <a href="#" class="hover:text-brand-400 transition-colors">
              <span class="sr-only">Instagram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { CheckIcon, ChevronDownIcon, StarIcon } from '@heroicons/vue/24/outline';
import AppButton from '../../components/ui/AppButton.vue';
import AppBadge from '../../components/ui/AppBadge.vue';
import AppAvatar from '../../components/ui/AppAvatar.vue';

const openFaqIndex = ref(null);

const faqs = [
  {
    question: 'IshTop qanday ishlaydi?',
    answer: 'IshTop orqali siz vakansiya yaratib, Telegram kanalimizda e\'lon qilasiz. Qiziquvchi nomzodlar savolnomangizga javob berishadi, siz ularni Kanban pipeline orqali boshqarasiz va eng yaxshilarini tanlab, ish bilan ta\'minlaysiz.',
  },
  {
    question: 'To\'lov qanday amalga oshiriladi?',
    answer: 'Siz faqat ariza kelganida to\'lov qilasiz (pay-per-application). To\'lovni Payme yoki Click orqali onlayn amalga oshirishingiz mumkin. Hech qanday oylik yoki yillik to\'lovlar yo\'q.',
  },
  {
    question: 'Savolnoma qanday yaratiladi?',
    answer: 'Bizning savolnoma builder\'imiz 6 xil savol turini qo\'llab-quvvatlaydi: ko\'p tanlov, bir tanlov, raqamli diapazon, ochiq javob, knockout savol va fayl yuklash. Har bir savol uchun ballar va og\'irlik (weight) belgilashingiz mumkin.',
  },
  {
    question: 'Talent Pool nima?',
    answer: 'Talent Pool - bu sizning keyingi vakansiyalar uchun nomzodlar bazasi. Hozirgi vakansiyaga mos kelmagan, lekin kelajakda foydali bo\'lishi mumkin bo\'lgan nomzodlarni saqlab qo\'yishingiz mumkin.',
  },
  {
    question: 'Bir nechta rekruter qo\'shsam bo\'ladimi?',
    answer: 'Ha, Professional va Enterprise rejalarda jamoa a\'zolarini qo\'shishingiz va ularga turli huquqlar berishingiz mumkin.',
  },
  {
    question: 'Telegram kanalda qanday e\'lon chiqadi?',
    answer: 'Biz sizning vakansiyangizni 25M+ foydalanuvchili Telegram kanalimizda e\'lon qilamiz. Nomzodlar botimiz orqali ariza topshirib, savolnomangizga javob berishadi.',
  },
];

function toggleFaq(index) {
  openFaqIndex.value = openFaqIndex.value === index ? null : index;
}
</script>
