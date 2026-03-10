<section class="relative bg-gradient-to-br from-brand-700 to-surface-900 overflow-hidden">
    {{-- Background decoration --}}
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full translate-y-1/2 -translate-x-1/3"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-brand-400/10 rounded-full blur-3xl"></div>
    </div>

    {{-- Floating icons (desktop only) --}}
    <div class="absolute inset-0 hidden lg:block pointer-events-none">
        {{-- Briefcase --}}
        <div class="absolute top-[15%] left-[8%] float-slow">
            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/10">
                <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                </svg>
            </div>
        </div>
        {{-- People --}}
        <div class="absolute top-[25%] right-[10%] float-medium" style="animation-delay: 1s;">
            <div class="w-14 h-14 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/10">
                <svg class="w-7 h-7 text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </div>
        </div>
        {{-- Clock --}}
        <div class="absolute bottom-[30%] left-[5%] float-fast" style="animation-delay: 0.5s;">
            <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-lg flex items-center justify-center border border-white/10">
                <svg class="w-5 h-5 text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        {{-- Chat --}}
        <div class="absolute top-[60%] right-[6%] float-slow" style="animation-delay: 2s;">
            <div class="w-11 h-11 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/10">
                <svg class="w-5 h-5 text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                </svg>
            </div>
        </div>
        {{-- Star --}}
        <div class="absolute top-[10%] left-[30%] float-medium" style="animation-delay: 1.5s;">
            <div class="w-9 h-9 bg-yellow-400/20 backdrop-blur-sm rounded-lg flex items-center justify-center border border-yellow-400/20">
                <svg class="w-4 h-4 text-yellow-300/80" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
        </div>
        {{-- Telegram --}}
        <div class="absolute bottom-[20%] right-[25%] float-fast" style="animation-delay: 0.8s;">
            <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-lg flex items-center justify-center border border-white/10">
                <svg class="w-5 h-5 text-white/60" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
            </div>
        </div>
        {{-- Checkmark --}}
        <div class="absolute bottom-[15%] left-[22%] float-medium" style="animation-delay: 2.5s;">
            <div class="w-9 h-9 bg-success-400/20 backdrop-blur-sm rounded-lg flex items-center justify-center border border-success-400/20">
                <svg class="w-4 h-4 text-success-300/80" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">
        {{-- Trust badge --}}
        <div class="flex justify-center mb-6">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white/90 text-sm px-4 py-2 rounded-full border border-white/10">
                <span class="w-2 h-2 bg-success-400 rounded-full animate-pulse"></span>
                {{ __('web.hero_trust') }}
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-4 leading-[1.1] tracking-tight">
                <span class="text-yellow-300 drop-shadow-[0_0_25px_rgba(253,224,71,0.5)]">{{ __('web.hero_title_accent') }}</span><br class="sm:hidden">
                {{ __('web.hero_title_rest') }}
            </h1>
            <p class="text-brand-100 text-base sm:text-lg md:text-xl max-w-2xl mx-auto leading-relaxed">
                {{ __('web.hero_subtitle') }}
            </p>
        </div>

        {{-- Search form --}}
        <form action="{{ route('vacancies.index') }}" method="GET"
              class="max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl p-1.5 sm:p-2 flex flex-col sm:flex-row gap-1.5 sm:gap-2 mb-10">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-surface-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="{{ __('web.search_placeholder') }}"
                       class="w-full pl-11 pr-4 py-3.5 rounded-xl border-0 bg-surface-50 text-surface-900 placeholder-surface-400 focus:ring-2 focus:ring-brand-500 focus:bg-white text-sm">
            </div>
            <div class="sm:w-44">
                <select name="region"
                        class="w-full py-3.5 px-4 rounded-xl border-0 bg-surface-50 text-surface-900 focus:ring-2 focus:ring-brand-500 text-sm appearance-none">
                    <option value="">{{ __('web.all_cities') }}</option>
                    @if(isset($regions))
                        @foreach($regions as $region => $count)
                            <option value="{{ $region }}" {{ request('region') === $region ? 'selected' : '' }}>{{ __('web.region_names.' . $region) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <button type="submit"
                    class="bg-brand-500 hover:bg-brand-600 text-white px-7 py-3.5 rounded-xl font-semibold transition-colors text-sm whitespace-nowrap shadow-sm">
                {{ __('web.search') }}
            </button>
        </form>

        {{-- Stats --}}
        @if(isset($stats))
            <div class="flex items-center justify-center gap-6 sm:gap-10">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ number_format($stats['vacancies']) }}+</div>
                    <div class="text-brand-200 text-xs sm:text-sm mt-0.5">{{ __('web.total_vacancies') }}</div>
                </div>
                <div class="w-px h-10 sm:h-12 bg-white/20"></div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ number_format($stats['companies']) }}+</div>
                    <div class="text-brand-200 text-xs sm:text-sm mt-0.5">{{ __('web.total_companies') }}</div>
                </div>
                <div class="w-px h-10 sm:h-12 bg-white/20"></div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ number_format($stats['workers']) }}+</div>
                    <div class="text-brand-200 text-xs sm:text-sm mt-0.5">{{ __('web.total_workers') }}</div>
                </div>
            </div>
        @endif
    </div>
</section>
