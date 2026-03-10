<div x-data="{ submitted: false, submitting: false }">
    <h2 class="text-lg font-semibold text-surface-900 mb-4">{{ __('web.apply_title') }}</h2>

    {{-- Telegram button --}}
    <a href="https://t.me/kadrgo_bot" target="_blank"
       class="flex items-center justify-center gap-2 bg-[#0088cc] hover:bg-[#0077b5] text-white px-4 py-3 rounded-xl font-medium transition-colors text-sm w-full mb-6">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
        {{ __('web.apply_via_telegram') }}
    </a>

    <div class="relative flex items-center justify-center my-6">
        <div class="border-t border-surface-200 w-full"></div>
        <span class="bg-white px-4 text-sm text-surface-400 absolute">{{ app()->getLocale() === 'ru' ? 'или' : 'yoki' }}</span>
    </div>

    {{-- Success message --}}
    <div x-show="submitted" x-cloak class="bg-success-50 border border-success-200 rounded-xl p-4 text-center">
        <svg class="w-10 h-10 text-success-500 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-success-700 font-medium">{{ __('web.application_sent') }}</p>
    </div>

    {{-- Web form --}}
    <form x-show="!submitted" method="POST" action="{{ route('vacancy.apply', $vacancy) }}"
          @submit.prevent="
              submitting = true;
              fetch($el.action, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                      'Accept': 'application/json',
                  },
                  body: JSON.stringify(Object.fromEntries(new FormData($el)))
              })
              .then(r => {
                  if (r.ok) { submitted = true; }
                  else { return r.json().then(d => { alert(d.message || 'Error'); }); }
              })
              .catch(() => alert('Error'))
              .finally(() => submitting = false);
          "
          class="space-y-4">

        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">{{ __('web.your_name') }} *</label>
            <input type="text" name="name" required maxlength="100"
                   class="w-full px-4 py-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                   placeholder="{{ __('web.your_name') }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">{{ __('web.your_phone') }} *</label>
            <input type="tel" name="phone" required maxlength="20"
                   class="w-full px-4 py-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                   placeholder="{{ __('web.phone_format') }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">{{ __('web.message') }}</label>
            <textarea name="message" rows="3" maxlength="1000"
                      class="w-full px-4 py-3 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm resize-none"
                      placeholder="{{ __('web.message') }}"></textarea>
        </div>

        <button type="submit" :disabled="submitting"
                class="w-full bg-brand-500 hover:bg-brand-600 disabled:bg-brand-300 text-white px-4 py-3 rounded-xl font-semibold transition-colors text-sm">
            <span x-show="!submitting">{{ __('web.send_application') }}</span>
            <span x-show="submitting" x-cloak class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                ...
            </span>
        </button>
    </form>
</div>
