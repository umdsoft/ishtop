<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
            class="flex items-center gap-1 text-surface-600 hover:text-brand-500 font-medium transition-colors text-sm">
        {{ app()->getLocale() === 'uz' ? 'UZ' : 'RU' }}
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-cloak x-transition
         class="absolute right-0 mt-2 w-24 bg-white rounded-xl shadow-hover border border-surface-100 overflow-hidden">
        <form method="POST" action="{{ route('lang.switch', 'uz') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-surface-50 {{ app()->getLocale() === 'uz' ? 'text-brand-500 font-semibold' : 'text-surface-600' }}">
                O'zbek
            </button>
        </form>
        <form method="POST" action="{{ route('lang.switch', 'ru') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-surface-50 {{ app()->getLocale() === 'ru' ? 'text-brand-500 font-semibold' : 'text-surface-600' }}">
                Русский
            </button>
        </form>
    </div>
</div>
