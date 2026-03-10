<template>
  <div class="min-h-screen bg-white">

    <!-- Navbar -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-lg border-b border-surface-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2.5">
          <div class="w-9 h-9 bg-brand-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg width="20" height="20" viewBox="0 0 48 48" fill="none">
              <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
            </svg>
          </div>
          <div class="flex flex-col leading-none">
            <span class="text-[12px] font-extrabold tracking-[0.5px] text-brand-500">KADR</span>
            <span class="text-[12px] font-black tracking-[1px] text-accent-500 flex items-center">GO<span class="w-1 h-1 rounded-full bg-accent-500 ml-1"></span></span>
          </div>
        </router-link>

        <!-- Desktop links -->
        <div class="hidden md:flex items-center gap-6">
          <a href="#features" class="text-sm text-surface-600 hover:text-brand-600 font-medium transition-colors">Xususiyatlar</a>
          <a href="#pricing" class="text-sm text-surface-600 hover:text-brand-600 font-medium transition-colors">Narxlar</a>
          <a href="#faq" class="text-sm text-surface-600 hover:text-brand-600 font-medium transition-colors">FAQ</a>
          <router-link to="/auth/login" class="text-sm text-surface-700 hover:text-brand-600 font-semibold transition-colors">Kirish</router-link>
          <router-link to="/auth/register" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-xl text-sm font-semibold transition-colors">
            Bepul boshlash
          </router-link>
        </div>

        <!-- Mobile menu -->
        <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-surface-600">
          <svg v-if="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Mobile dropdown -->
      <div v-if="mobileMenu" class="md:hidden bg-white border-t border-surface-100 px-4 py-4 space-y-3">
        <a href="#features" class="block text-surface-700 font-medium py-2" @click="mobileMenu = false">Xususiyatlar</a>
        <a href="#pricing" class="block text-surface-700 font-medium py-2" @click="mobileMenu = false">Narxlar</a>
        <a href="#faq" class="block text-surface-700 font-medium py-2" @click="mobileMenu = false">FAQ</a>
        <router-link to="/auth/login" class="block text-surface-700 font-medium py-2" @click="mobileMenu = false">Kirish</router-link>
        <router-link to="/auth/register" class="block text-center bg-brand-500 text-white px-4 py-3 rounded-xl font-semibold" @click="mobileMenu = false">Bepul boshlash</router-link>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-28 pb-20 md:pt-36 md:pb-28 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-surface-50 via-white to-brand-50"></div>
      <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-brand-100/30 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-accent-100/20 rounded-full blur-3xl"></div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2 bg-brand-50 text-brand-600 text-sm font-medium px-4 py-1.5 rounded-full border border-brand-100 mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            O'zbekistonning #1 rekruting platformasi
          </div>

          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-surface-900 leading-tight mb-6">
            Biznesingiz uchun
            <span class="text-brand-500">kerakli hodimni</span>
            tez toping
          </h1>

          <p class="text-lg md:text-xl text-surface-500 mb-8 max-w-2xl mx-auto leading-relaxed">
            CRM tizimi, savolnoma builder, Kanban pipeline — barcha kerakli vositalar bitta platformada. Telegram orqali minglab nomzodlarga yeting.
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
            <router-link
              to="/auth/register"
              class="px-8 py-4 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-semibold text-lg transition-all shadow-lg shadow-brand-500/25 hover:shadow-xl hover:-translate-y-0.5"
            >
              Bepul sinab ko'rish
            </router-link>
            <a
              href="#pricing"
              class="px-8 py-4 bg-white text-surface-700 rounded-xl font-semibold text-lg transition-colors border border-surface-200 hover:border-brand-200 hover:text-brand-600"
            >
              Tariflarni ko'rish
            </a>
          </div>

          <p class="text-sm text-surface-400">
            1-2 ta vakansiya qo'shmoqchimisiz?
            <a href="https://t.me/kadrgobot" target="_blank" class="text-brand-500 hover:text-brand-600 font-medium underline underline-offset-2">Telegram bot</a>
            orqali bepul joylashtiring
          </p>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="py-12 border-y border-surface-100 bg-surface-50/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600">14</div>
            <div class="text-surface-500 mt-1 text-sm">Viloyat qamrovi</div>
          </div>
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600">50K+</div>
            <div class="text-surface-500 mt-1 text-sm">Telegram auditoriyasi</div>
          </div>
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600">120+</div>
            <div class="text-surface-500 mt-1 text-sm">Faol kompaniyalar</div>
          </div>
          <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-brand-600">5 daq</div>
            <div class="text-surface-500 mt-1 text-sm">Vakansiya yaratish vaqti</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 md:py-28">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 mb-4">
            Rekruting jarayonini avtomatlashtiring
          </h2>
          <p class="text-lg text-surface-500 max-w-2xl mx-auto">
            Vakansiya yaratishdan nomzod tanlashgacha — barcha bosqichlar nazoratda
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="feature in features" :key="feature.title"
               class="group p-6 bg-white rounded-2xl border border-surface-100 hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50 transition-all">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 text-2xl" :class="feature.bg">
              {{ feature.icon }}
            </div>
            <h3 class="text-lg font-semibold text-surface-900 mb-2">{{ feature.title }}</h3>
            <p class="text-surface-500 text-sm leading-relaxed">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section class="py-20 bg-surface-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 mb-4">Qanday ishlaydi?</h2>
          <p class="text-lg text-surface-500">4 oddiy qadam bilan hodim toping</p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
          <div v-for="(step, i) in steps" :key="i" class="text-center relative">
            <div class="w-14 h-14 rounded-full bg-brand-500 text-white font-bold text-xl flex items-center justify-center mx-auto mb-4">
              {{ i + 1 }}
            </div>
            <h3 class="font-semibold text-surface-900 mb-2">{{ step.title }}</h3>
            <p class="text-sm text-surface-500">{{ step.desc }}</p>
            <!-- Arrow -->
            <svg v-if="i < 3" class="hidden md:block absolute top-7 -right-4 w-8 h-8 text-surface-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
            </svg>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 md:py-28">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 mb-4">Tariflar</h2>
          <p class="text-lg text-surface-500 max-w-2xl mx-auto">
            Biznesingiz hajmiga mos tarifni tanlang. Barcha tariflar 7 kunlik bepul sinov bilan boshlanadi.
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 lg:gap-8 max-w-5xl mx-auto">
          <!-- Starter -->
          <div class="bg-white rounded-2xl border border-surface-200 p-8 flex flex-col">
            <div class="mb-6">
              <h3 class="text-xl font-bold text-surface-900 mb-1">Starter</h3>
              <p class="text-surface-500 text-sm">Kichik va o'rta bizneslar uchun</p>
            </div>
            <div class="mb-6">
              <span class="text-4xl font-bold text-surface-900">349,000</span>
              <span class="text-surface-500"> so'm/oy</span>
            </div>
            <ul class="space-y-3 mb-8 flex-1">
              <li v-for="item in starterFeatures" :key="item" class="flex items-start gap-2.5">
                <svg class="w-5 h-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="text-surface-600 text-sm">{{ item }}</span>
              </li>
            </ul>
            <router-link to="/auth/register" class="block text-center py-3 rounded-xl font-semibold text-sm border-2 border-surface-200 text-surface-700 hover:border-brand-500 hover:text-brand-600 transition-colors">
              Boshlash
            </router-link>
          </div>

          <!-- Professional -->
          <div class="bg-white rounded-2xl border-2 border-brand-500 p-8 flex flex-col relative shadow-lg shadow-brand-100">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-brand-500 text-white text-xs font-bold px-4 py-1 rounded-full">
              Mashhur
            </div>
            <div class="mb-6">
              <h3 class="text-xl font-bold text-surface-900 mb-1">Professional</h3>
              <p class="text-surface-500 text-sm">O'sib borayotgan kompaniyalar uchun</p>
            </div>
            <div class="mb-6">
              <span class="text-4xl font-bold text-surface-900">499,000</span>
              <span class="text-surface-500"> so'm/oy</span>
            </div>
            <ul class="space-y-3 mb-8 flex-1">
              <li v-for="item in proFeatures" :key="item" class="flex items-start gap-2.5">
                <svg class="w-5 h-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="text-surface-600 text-sm">{{ item }}</span>
              </li>
            </ul>
            <router-link to="/auth/register" class="block text-center py-3 rounded-xl font-semibold text-sm bg-brand-500 hover:bg-brand-600 text-white transition-colors">
              Boshlash
            </router-link>
          </div>

          <!-- Enterprise -->
          <div class="bg-white rounded-2xl border border-surface-200 p-8 flex flex-col">
            <div class="mb-6">
              <h3 class="text-xl font-bold text-surface-900 mb-1">Enterprise</h3>
              <p class="text-surface-500 text-sm">Yirik korporativ kompaniyalar uchun</p>
            </div>
            <div class="mb-6">
              <span class="text-4xl font-bold text-surface-900">Kelishuv</span>
            </div>
            <ul class="space-y-3 mb-8 flex-1">
              <li v-for="item in enterpriseFeatures" :key="item" class="flex items-start gap-2.5">
                <svg class="w-5 h-5 text-brand-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="text-surface-600 text-sm">{{ item }}</span>
              </li>
            </ul>
            <a href="https://t.me/kadrgobot" target="_blank" class="block text-center py-3 rounded-xl font-semibold text-sm border-2 border-surface-200 text-surface-700 hover:border-brand-500 hover:text-brand-600 transition-colors">
              Bog'lanish
            </a>
          </div>
        </div>

        <p class="text-center text-sm text-surface-400 mt-8">
          1-2 ta vakansiya joylashtirish uchun
          <a href="https://t.me/kadrgobot" target="_blank" class="text-brand-500 hover:text-brand-600 font-medium underline underline-offset-2">Telegram bot</a>dan
          bepul foydalaning
        </p>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-surface-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 mb-4">Mijozlar fikri</h2>
          <p class="text-lg text-surface-500">KadrGo orqali jamoalarini kengaytirgan kompaniyalar</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
          <div v-for="t in testimonials" :key="t.name" class="bg-white rounded-2xl p-6 border border-surface-100">
            <div class="flex items-center gap-0.5 mb-4">
              <svg v-for="i in 5" :key="i" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <p class="text-surface-600 text-sm leading-relaxed mb-5">"{{ t.text }}"</p>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm" :class="t.avatarBg">
                {{ t.name.charAt(0) }}
              </div>
              <div>
                <p class="font-semibold text-surface-900 text-sm">{{ t.name }}</p>
                <p class="text-xs text-surface-500">{{ t.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-surface-900 mb-4">Ko'p beriladigan savollar</h2>
        </div>
        <div class="space-y-3">
          <div v-for="(faq, i) in faqs" :key="i" class="border border-surface-200 rounded-xl overflow-hidden">
            <button
              class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-surface-50 transition-colors"
              @click="openFaq = openFaq === i ? null : i"
            >
              <span class="font-semibold text-surface-900 text-sm pr-4">{{ faq.q }}</span>
              <svg :class="['w-5 h-5 text-surface-400 flex-shrink-0 transition-transform', openFaq === i ? 'rotate-180' : '']" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div v-if="openFaq === i" class="px-6 pb-4">
              <p class="text-surface-500 text-sm leading-relaxed">{{ faq.a }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-gradient-to-br from-brand-500 to-brand-600 relative overflow-hidden">
      <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
      <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/3"></div>
      <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
          Bugun boshlab ko'ring
        </h2>
        <p class="text-brand-100 text-lg mb-8 max-w-xl mx-auto">
          7 kunlik bepul sinov — hech qanday to'lov talab qilinmaydi. Birinchi vakansiyangizni daqiqalar ichida joylashtiring.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/auth/register"
            class="px-8 py-4 bg-white text-brand-600 rounded-xl font-semibold text-lg hover:bg-brand-50 transition-colors shadow-lg"
          >
            Bepul ro'yxatdan o'tish
          </router-link>
          <a
            href="https://t.me/kadrgobot"
            target="_blank"
            class="px-8 py-4 rounded-xl font-semibold text-lg text-white border-2 border-white/30 hover:bg-white/10 transition-colors"
          >
            Telegram bot
          </a>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-surface-900 text-surface-400 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
          <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-9 h-9 bg-brand-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg width="20" height="20" viewBox="0 0 48 48" fill="none">
                  <path d="M15 14L15 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M15 24L27 14" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M15 24L27 34" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M30 17L35 17L35 31L30 31" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.7"/>
                </svg>
              </div>
              <span class="text-white font-bold text-lg">KadrGo</span>
            </div>
            <p class="text-sm leading-relaxed">Telegram asosidagi rekruting platformasi. Hodim topish hech qachon bunchalik oson bo'lmagan.</p>
          </div>

          <div>
            <h4 class="text-white font-semibold text-sm mb-4">Platforma</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#features" class="hover:text-white transition-colors">Xususiyatlar</a></li>
              <li><a href="#pricing" class="hover:text-white transition-colors">Narxlar</a></li>
              <li><a href="/" class="hover:text-white transition-colors">Vakansiyalar</a></li>
              <li><a href="https://t.me/kadrgobot" target="_blank" class="hover:text-white transition-colors">Telegram bot</a></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-semibold text-sm mb-4">Kompaniya</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="https://t.me/kadrgobot" target="_blank" class="hover:text-white transition-colors">Bog'lanish</a></li>
              <li><a href="https://instagram.com/kadrgo.uz" target="_blank" class="hover:text-white transition-colors">Instagram</a></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-semibold text-sm mb-4">Huquqiy</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">Foydalanish shartlari</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Maxfiylik siyosati</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-surface-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-sm">&copy; 2025 KadrGo. Barcha huquqlar himoyalangan.</p>
          <div class="flex gap-3">
            <a href="https://t.me/kadrgobot" target="_blank" class="w-9 h-9 bg-surface-800 hover:bg-brand-500 rounded-lg flex items-center justify-center transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            </a>
            <a href="https://instagram.com/kadrgo.uz" target="_blank" class="w-9 h-9 bg-surface-800 hover:bg-brand-500 rounded-lg flex items-center justify-center transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const mobileMenu = ref(false);
const openFaq = ref(null);

const features = [
  { icon: '📝', title: 'Savolnoma builder', desc: '6 xil savol turi: ko\'p tanlov, raqamli, ochiq javob, knockout va boshqalar. Nomzodlarni avtomatik baholash.', bg: 'bg-brand-50' },
  { icon: '📊', title: 'Kanban pipeline', desc: 'Drag-and-drop bilan nomzodlarni bosqichma-bosqich boshqaring. Yangi → Tekshirilmoqda → Intervyu → Qabul qilindi.', bg: 'bg-blue-50' },
  { icon: '🎯', title: 'Talent Pool', desc: 'Barcha nomzodlar bazasi. Keyingi vakansiyalar uchun mos nomzodlarni toping va saqlang.', bg: 'bg-emerald-50' },
  { icon: '📱', title: 'Telegram integratsiya', desc: 'Vakansiyalar Telegram kanalda avtomatik chop etiladi. Nomzodlar bot orqali ariza topshiradi.', bg: 'bg-violet-50' },
  { icon: '📈', title: 'Analitika', desc: 'Real-time statistikalar: ko\'rishlar, arizalar, konversiya, o\'rtacha yopish vaqti va boshqalar.', bg: 'bg-amber-50' },
  { icon: '💰', title: 'Tez to\'lov', desc: 'Payme va Click orqali to\'lovlar. Avtomatik hisob-kitob va cheklar.', bg: 'bg-rose-50' },
];

const steps = [
  { title: 'Ro\'yxatdan o\'ting', desc: 'Kompaniya profilini to\'ldiring va CRM ga kiring' },
  { title: 'Vakansiya yarating', desc: 'Savolnomani ulang va Telegram kanalga chop eting' },
  { title: 'Arizalarni ko\'ring', desc: 'Nomzodlar javoblarini baholang va eng yaxshilarini tanlang' },
  { title: 'Hodimni tanlang', desc: 'Intervyu o\'tkazing va jamoangizga qo\'shing' },
];

const starterFeatures = [
  '15 ta vakansiya / oy',
  'CRM tizimi (Kanban pipeline)',
  'Savolnoma builder',
  'Telegram kanalda e\'lon',
  'Asosiy analitika',
  'Email qo\'llab-quvvatlash',
];

const proFeatures = [
  'Cheksiz vakansiyalar',
  'Barcha Starter xususiyatlar',
  'Mos nomzodlarni cheksiz ko\'rish',
  'Talent Pool — to\'liq baza',
  'Kengaytirilgan analitika va hisobotlar',
  'Telegram reklama integratsiya',
  'Ustuvor qo\'llab-quvvatlash',
];

const enterpriseFeatures = [
  'Barcha Professional xususiyatlar',
  'Jamoaviy ishlash (ko\'p foydalanuvchi)',
  'Rollar va huquqlar boshqaruvi',
  'API access',
  'Shaxsiy menejer',
  'Maxsus integratsiyalar',
];

const testimonials = [
  { name: 'Aziz Karimov', role: 'Grand Hotel, HR Director', text: 'KadrGo bizga oyiga 50+ nomzodni tekshirishda yordam berdi. Savolnoma tizimi 100 ta arizadan 10 tasini 30 sekundda ajratib beradi.', avatarBg: 'bg-brand-500' },
  { name: 'Madina Alieva', role: 'TechCorp, Recruiter', text: 'Kanban pipeline va Telegram integratsiya juda qulaylik yaratdi. Nomzodlar bilan tez bog\'lanamiz, jarayon 3 barobar tezlashdi.', avatarBg: 'bg-violet-500' },
  { name: 'Jahongir Rahimov', role: 'FoodChain, CEO', text: 'Har oyda 10+ filialga hodim kerak. KadrGo Professional tarifi bilan cheksiz vakansiya va nomzod bazasi — bizga aynan shu kerak edi.', avatarBg: 'bg-amber-500' },
];

const faqs = [
  { q: 'KadrGo nima va kimlar uchun?', a: 'KadrGo — Telegram asosidagi rekruting platformasi. Bizneslar va rekruterlar uchun: CRM tizimi, savolnoma builder, Kanban pipeline va boshqa vositalar bilan hodim topish jarayonini avtomatlashtiradi.' },
  { q: '1-2 ta vakansiya uchun ham to\'lashim kerakmi?', a: 'Yo\'q! Agar 1-2 ta vakansiya joylashtirmoqchi bo\'lsangiz, Telegram botimiz (@kadrgobot) orqali bepul joylashtiring. Tariflar CRM va professional vositalar kerak bo\'lganda foydali.' },
  { q: 'Bepul sinov qanday ishlaydi?', a: 'Ro\'yxatdan o\'tganingizda 7 kunlik bepul sinov davri boshlanadi. Bu vaqtda barcha Professional xususiyatlardan foydalanishingiz mumkin. Sinov tugagach, mos tarifni tanlaysiz.' },
  { q: 'Savolnoma tizimi qanday ishlaydi?', a: 'Savolnoma builder 6 xil savol turini qo\'llab-quvvatlaydi. Har bir savol uchun ball va og\'irlik belgilaysiz. Nomzodlar javob berganda avtomatik baholanadi va Kanban board da tartiblanadi.' },
  { q: 'Telegram kanalda vakansiya qanday chiqadi?', a: 'Vakansiya yaratib "Chop etish" ni bossangiz, Telegram kanalimizda chiqadi. Nomzodlar @kadrgobot orqali ariza topshiradi va savolnomangizga javob beradi.' },
  { q: 'Enterprise tarifi nimalar bilan farqlanadi?', a: 'Enterprise korporativ kompaniyalar uchun: jamoaviy ishlash (bir nechta rekruter), rollar va huquqlar boshqaruvi, API access, shaxsiy menejer va maxsus integratsiyalar. Narx kelishuv asosida.' },
];
</script>
