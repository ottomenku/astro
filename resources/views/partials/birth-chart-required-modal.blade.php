<div id="birthChartRequiredModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60" data-birth-chart-modal-close></div>
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl p-6 text-gray-900">
        <h3 class="text-lg font-semibold mb-2">{{ __('public.birth_chart_required_title') }}</h3>
        <p class="text-sm text-gray-600 mb-5">{{ __('public.birth_chart_required_text') }}</p>
        <div class="flex flex-wrap gap-3 justify-end">
            <button type="button" class="px-4 py-2 text-sm rounded border border-gray-300" data-birth-chart-modal-close>{{ __('public.close') }}</button>
            <a href="{{ route('profile.birth-charts.create') }}" class="px-4 py-2 text-sm rounded bg-indigo-600 text-white font-medium hover:bg-indigo-700">{{ __('public.birth_chart_add_btn') }}</a>
        </div>
    </div>
</div>

<script>
    window.showBirthChartRequiredModal = function () {
        document.getElementById('birthChartRequiredModal')?.classList.remove('hidden');
    };
    document.querySelectorAll('[data-birth-chart-modal-close]').forEach((el) => {
        el.addEventListener('click', () => {
            document.getElementById('birthChartRequiredModal')?.classList.add('hidden');
        });
    });
</script>
