@php $lang = app()->getLocale(); @endphp

<a href="{{ $vacancy->slug ? route('vacancies.show', $vacancy) : '#' }}"
   class="group relative flex flex-col bg-white rounded-xl border border-surface-100 hover:border-brand-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

    {{-- TOP/Urgent highlight bar --}}
    @if($vacancy->isTopActive())
        <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-warning-400 to-warning-500"></div>
    @elseif($vacancy->is_urgent && $vacancy->urgent_until?->isFuture())
        <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-danger-400 to-danger-500"></div>
    @endif

    <div class="p-4 flex flex-col flex-1">
        {{-- Row 1: Title + badges --}}
        <div class="flex items-start justify-between gap-2 mb-2">
            <h3 class="text-sm font-semibold text-surface-900 group-hover:text-brand-600 transition-colors line-clamp-2 leading-snug">
                {{ $vacancy->title($lang) }}
            </h3>
            <div class="flex items-center gap-1 shrink-0 mt-0.5">
                @if($vacancy->isTopActive())
                    <span class="bg-gradient-to-r from-warning-500 to-warning-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">TOP</span>
                @endif
                @if($vacancy->is_urgent && $vacancy->urgent_until?->isFuture())
                    <span class="bg-danger-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">{{ __('web.urgent') }}</span>
                @endif
            </div>
        </div>

        {{-- Row 2: Salary --}}
        <div class="mb-2.5">
            <span class="text-brand-600 font-bold text-sm">
                @if($vacancy->salary_type === 'negotiable')
                    {{ __('web.negotiable') }}
                @elseif($vacancy->salary_min && $vacancy->salary_max)
                    {{ number_format($vacancy->salary_min) }} - {{ number_format($vacancy->salary_max) }} {{ __('web.som') }}
                @elseif($vacancy->salary_min)
                    {{ __('web.salary_from') }} {{ number_format($vacancy->salary_min) }} {{ __('web.som') }}
                @elseif($vacancy->salary_max)
                    {{ __('web.salary_to') }} {{ number_format($vacancy->salary_max) }} {{ __('web.som') }}
                @else
                    {{ __('web.negotiable') }}
                @endif
            </span>
        </div>

        {{-- Row 3: Company --}}
        <div class="flex items-center gap-1.5 mb-3">
            @if($vacancy->employer?->logo_url)
                <img src="{{ $vacancy->employer->logo_url }}" alt="" class="w-4 h-4 rounded object-cover" loading="lazy">
            @else
                <div class="w-4 h-4 rounded bg-surface-100 flex items-center justify-center">
                    <svg class="w-2.5 h-2.5 text-surface-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            @endif
            <span class="text-xs text-surface-500 truncate">{{ $vacancy->company_name }}</span>
            @if($vacancy->employer?->verification_level === 'verified')
                <svg class="w-3.5 h-3.5 text-brand-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            @endif
        </div>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Row 4: Meta tags --}}
        <div class="flex flex-wrap items-center gap-1.5 text-xs text-surface-400">
            @if($vacancy->city)
                <span class="inline-flex items-center gap-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    {{ $vacancy->district ?? $vacancy->city }}
                </span>
            @endif
            @if($vacancy->work_type)
                <span class="bg-surface-50 px-1.5 py-0.5 rounded text-surface-500">{{ __('web.' . $vacancy->work_type->value) }}</span>
            @endif
            @if(!$vacancy->experience_required)
                <span class="bg-success-50 text-success-600 px-1.5 py-0.5 rounded">{{ __('web.no_experience') }}</span>
            @endif
            @if($vacancy->published_at)
                <span class="ml-auto text-surface-400">{{ $vacancy->published_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>
</a>
