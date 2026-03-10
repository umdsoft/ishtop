@extends('layouts.website')

@section('title', __('web.vacancies') . ' — KadrGo')
@section('meta_description', __('web.hero_subtitle'))

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-surface-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-500 transition-colors inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
                </svg>
                {{ __('web.home') }}
            </a>
            <svg class="w-3.5 h-3.5 text-surface-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-surface-600 font-medium">{{ __('web.vacancies') }}</span>
            @if($expandedRoot)
                @php $breadcrumbCat = $categories->firstWhere('slug', $expandedRoot); @endphp
                @if($breadcrumbCat)
                    <svg class="w-3.5 h-3.5 text-surface-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-surface-600 font-medium">{{ $breadcrumbCat->name($lang) }}</span>
                @endif
            @endif
        </nav>

        <div class="flex flex-col lg:flex-row gap-6" x-data="{ filtersOpen: false }">

            {{-- ===== SIDEBAR ===== --}}
            <aside class="w-full lg:w-72 xl:w-80 shrink-0">
                {{-- Mobile toggle --}}
                <button @click="filtersOpen = !filtersOpen" type="button"
                        class="flex items-center justify-between w-full bg-white border border-surface-200 rounded-xl px-4 py-3 lg:hidden mb-4 shadow-sm">
                    <span class="flex items-center gap-2 text-sm font-medium text-surface-700">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        {{ __('web.filters') }}
                        @php
                            $activeFilters = collect(['q', 'category', 'region', 'work_type', 'salary_min', 'salary_max'])->filter(fn($f) => request()->filled($f))->count();
                        @endphp
                        @if($activeFilters > 0)
                            <span class="bg-brand-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">{{ $activeFilters }}</span>
                        @endif
                    </span>
                    <svg class="w-5 h-5 text-surface-400 transition-transform" :class="{ 'rotate-180': filtersOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Filter panel --}}
                <div :class="{ 'hidden lg:block': !filtersOpen }" class="space-y-4">

                    {{-- Search box --}}
                    <form action="{{ route('vacancies.index') }}" method="GET" id="filterForm"
                          class="bg-white rounded-2xl border border-surface-100 shadow-sm p-4">
                        <div class="relative mb-3">
                            <svg class="w-4 h-4 text-surface-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="{{ __('web.search_placeholder') }}"
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm bg-surface-50">
                        </div>
                        {{-- Preserve other filters --}}
                        @php $catParam = request('category'); @endphp
                        @if($catParam)
                            @if(is_array($catParam))
                                @foreach($catParam as $cs)
                                    <input type="hidden" name="category[]" value="{{ $cs }}">
                                @endforeach
                            @else
                                <input type="hidden" name="category" value="{{ $catParam }}">
                            @endif
                        @endif
                        @if(request('region'))<input type="hidden" name="region" value="{{ request('region') }}">@endif
                        @if(request('work_type'))<input type="hidden" name="work_type" value="{{ request('work_type') }}">@endif
                        @if(request('salary_min'))<input type="hidden" name="salary_min" value="{{ request('salary_min') }}">@endif
                        @if(request('salary_max'))<input type="hidden" name="salary_max" value="{{ request('salary_max') }}">@endif
                        @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                        <button type="submit"
                                class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            {{ __('web.search') }}
                        </button>
                    </form>

                    {{-- Categories with subcategory checkboxes --}}
                    <div class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-surface-100 flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">{{ __('web.category') }}</span>
                        </div>
                        <div class="p-2 max-h-[420px] overflow-y-auto">
                            {{-- All categories --}}
                            <a href="{{ route('vacancies.index', request()->except(['category', 'page'])) }}"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ !request('category') ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-surface-600 hover:bg-surface-50' }}">
                                <svg class="w-4 h-4 {{ !request('category') ? 'text-brand-500' : 'text-surface-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                {{ __('web.all_categories') }}
                            </a>

                            @foreach($categories as $cat)
                                @php $isRootActive = $expandedRoot === $cat->slug; @endphp
                                <div>
                                    {{-- Root category link --}}
                                    <a href="{{ route('vacancies.index', array_merge(request()->except(['category', 'page']), ['category' => $cat->slug])) }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors {{ $isRootActive ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-surface-600 hover:bg-surface-50' }}">
                                        <span class="truncate">{{ $cat->name($lang) }}</span>
                                        @if($cat->vacancies_count > 0)
                                            <span class="text-xs {{ $isRootActive ? 'text-brand-400' : 'text-surface-400' }} tabular-nums">{{ $cat->vacancies_count }}</span>
                                        @endif
                                    </a>

                                    {{-- Subcategories with checkboxes --}}
                                    @if($cat->children->isNotEmpty() && $isRootActive)
                                        <div class="ml-4 pl-3 border-l-2 border-brand-100 mb-1 space-y-0.5">
                                            @foreach($cat->children as $child)
                                                <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-md text-xs cursor-pointer hover:bg-surface-50 transition-colors">
                                                    <input type="checkbox"
                                                           class="sub-cat-cb rounded border-surface-300 text-brand-500 focus:ring-brand-500 focus:ring-offset-0 w-3.5 h-3.5 cursor-pointer"
                                                           value="{{ $child->slug }}"
                                                           {{ in_array($child->slug, $selectedSubs) ? 'checked' : '' }}
                                                           onchange="applySubCategories()">
                                                    <span class="truncate flex-1 {{ in_array($child->slug, $selectedSubs) ? 'text-brand-600 font-medium' : 'text-surface-500' }}">{{ $child->name($lang) }}</span>
                                                    @if($child->vacancies_count > 0)
                                                        <span class="text-[11px] text-surface-400 tabular-nums">{{ $child->vacancies_count }}</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Region filter --}}
                    <div class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-surface-100 flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">{{ __('web.region') }}</span>
                        </div>
                        <div class="p-2 max-h-[300px] overflow-y-auto">
                            <a href="{{ route('vacancies.index', request()->except(['region', 'page'])) }}"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors {{ !request('region') ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-surface-600 hover:bg-surface-50' }}">
                                {{ __('web.all_regions') }}
                            </a>
                            @foreach($regions as $region => $count)
                                <a href="{{ route('vacancies.index', array_merge(request()->except(['region', 'page']), ['region' => $region])) }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors {{ request('region') === $region ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-surface-600 hover:bg-surface-50' }}">
                                    <span class="truncate">{{ $region }}</span>
                                    <span class="text-xs {{ request('region') === $region ? 'text-brand-400' : 'text-surface-400' }} tabular-nums">{{ $count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Work type + Salary + Sort --}}
                    <div class="bg-white rounded-2xl border border-surface-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-surface-100 flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">{{ __('web.additional_filters') }}</span>
                        </div>
                        <form action="{{ route('vacancies.index') }}" method="GET" class="p-4 space-y-4">
                            {{-- Preserve other filters --}}
                            @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                            @php $catParam2 = request('category'); @endphp
                            @if($catParam2)
                                @if(is_array($catParam2))
                                    @foreach($catParam2 as $cs2)
                                        <input type="hidden" name="category[]" value="{{ $cs2 }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="category" value="{{ $catParam2 }}">
                                @endif
                            @endif
                            @if(request('region'))<input type="hidden" name="region" value="{{ request('region') }}">@endif

                            {{-- Work type --}}
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('web.work_type') }}
                                </label>
                                <select name="work_type"
                                        class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50 appearance-none">
                                    <option value="">{{ __('web.all_types') }}</option>
                                    <option value="full_time" {{ request('work_type') === 'full_time' ? 'selected' : '' }}>{{ __('web.full_time') }}</option>
                                    <option value="part_time" {{ request('work_type') === 'part_time' ? 'selected' : '' }}>{{ __('web.part_time') }}</option>
                                    <option value="remote" {{ request('work_type') === 'remote' ? 'selected' : '' }}>{{ __('web.remote') }}</option>
                                    <option value="temporary" {{ request('work_type') === 'temporary' ? 'selected' : '' }}>{{ __('web.temporary') }}</option>
                                </select>
                            </div>

                            {{-- Salary range --}}
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('web.salary') }} ({{ __('web.som') }})
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="salary_min" value="{{ request('salary_min') }}"
                                           placeholder="{{ __('web.salary_from') }}"
                                           class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50">
                                    <span class="text-surface-300 font-medium">—</span>
                                    <input type="number" name="salary_max" value="{{ request('salary_max') }}"
                                           placeholder="{{ __('web.salary_to') }}"
                                           class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50">
                                </div>
                            </div>

                            {{-- Sort --}}
                            <div>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-surface-500 uppercase tracking-wide mb-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                                    </svg>
                                    {{ __('web.sort') }}
                                </label>
                                <select name="sort"
                                        class="w-full py-2.5 px-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 text-sm bg-surface-50 appearance-none">
                                    <option value="">{{ __('web.sort_newest') }}</option>
                                    <option value="salary_asc" {{ request('sort') === 'salary_asc' ? 'selected' : '' }}>{{ __('web.sort_salary_asc') }}</option>
                                    <option value="salary_desc" {{ request('sort') === 'salary_desc' ? 'selected' : '' }}>{{ __('web.sort_salary_desc') }}</option>
                                </select>
                            </div>

                            {{-- Action buttons --}}
                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                        class="flex-1 bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-xl font-semibold text-sm transition-colors flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    {{ __('web.apply_filters') }}
                                </button>
                                @if(request()->hasAny(['q', 'category', 'region', 'work_type', 'sort', 'salary_min', 'salary_max']))
                                    <a href="{{ route('vacancies.index') }}"
                                       class="px-4 py-2.5 rounded-xl border border-surface-200 text-sm text-surface-600 hover:bg-surface-50 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        {{ __('web.reset_filters') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                </div>
            </aside>

            {{-- ===== MAIN CONTENT ===== --}}
            <div class="flex-1 min-w-0">
                {{-- Results header --}}
                <div class="flex items-center justify-between mb-5">
                    <p class="text-sm text-surface-500 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-surface-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        </svg>
                        {{ __('web.results_count', ['count' => $vacancies->total()]) }}
                    </p>

                    {{-- Active filter chips --}}
                    @if(request()->hasAny(['q', 'category', 'region', 'work_type']))
                        <div class="hidden sm:flex items-center gap-2 flex-wrap">
                            @if(request('q'))
                                <a href="{{ route('vacancies.index', request()->except(['q', 'page'])) }}"
                                   class="inline-flex items-center gap-1 bg-brand-50 text-brand-600 text-xs px-2.5 py-1 rounded-lg hover:bg-brand-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    "{{ Str::limit(request('q'), 15) }}"
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endif
                            @if($expandedRoot)
                                @php $chipCat = $categories->firstWhere('slug', $expandedRoot); @endphp
                                @if($chipCat)
                                    <a href="{{ route('vacancies.index', request()->except(['category', 'page'])) }}"
                                       class="inline-flex items-center gap-1 bg-brand-50 text-brand-600 text-xs px-2.5 py-1 rounded-lg hover:bg-brand-100 transition-colors">
                                        {{ $chipCat->name($lang) }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                @endif
                            @endif
                            @if(request('region'))
                                <a href="{{ route('vacancies.index', request()->except(['region', 'page'])) }}"
                                   class="inline-flex items-center gap-1 bg-brand-50 text-brand-600 text-xs px-2.5 py-1 rounded-lg hover:bg-brand-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    {{ request('region') }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endif
                            @if(request('work_type'))
                                <a href="{{ route('vacancies.index', request()->except(['work_type', 'page'])) }}"
                                   class="inline-flex items-center gap-1 bg-brand-50 text-brand-600 text-xs px-2.5 py-1 rounded-lg hover:bg-brand-100 transition-colors">
                                    {{ __('web.' . request('work_type')) }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if($vacancies->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($vacancies as $vacancy)
                            @include('website.partials.vacancy-card', ['vacancy' => $vacancy])
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $vacancies->links() }}
                    </div>
                @else
                    <div class="text-center py-20 bg-white rounded-2xl border border-surface-100">
                        <div class="w-20 h-20 bg-surface-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-surface-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-surface-700 mb-2">{{ __('web.no_results') }}</h3>
                        <p class="text-surface-400 text-sm mb-6">{{ __('web.no_results_hint') }}</p>
                        <a href="{{ route('vacancies.index') }}"
                           class="inline-flex items-center gap-2 bg-brand-50 text-brand-600 hover:bg-brand-100 font-medium text-sm px-5 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('web.reset_filters') }}
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Subcategory checkbox navigation --}}
    <script>
    function applySubCategories() {
        var checked = [];
        var allBoxes = document.querySelectorAll('.sub-cat-cb');
        allBoxes.forEach(function(cb) {
            if (cb.checked) checked.push(cb.value);
        });

        var url = new URL(window.location);
        var params = new URLSearchParams();

        // Copy all non-category params
        url.searchParams.forEach(function(value, key) {
            if (key !== 'category' && key !== 'category[]' && key !== 'page') {
                params.append(key, value);
            }
        });

        var allChecked = checked.length === allBoxes.length;
        var noneChecked = checked.length === 0;

        if (noneChecked || allChecked) {
            // All or none checked → show root category (all vacancies in this category)
            params.append('category', '{{ $expandedRoot ?? '' }}');
        } else {
            // Specific subcategories checked
            checked.forEach(function(slug) {
                params.append('category[]', slug);
            });
        }

        url.search = params.toString();
        window.location = url;
    }
    </script>

@endsection
