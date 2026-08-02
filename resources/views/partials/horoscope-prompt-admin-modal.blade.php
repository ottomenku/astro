@if (\App\Support\SiteMode::canUseAdminUi())
<div id="horoscopePromptAdminModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" data-prompt-modal-backdrop></div>

        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4 max-h-[85vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" id="horoscopePromptAdminModalTitle">
                            {{ __('horoscope.prompt_settings_title') }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1" id="horoscopePromptAdminModalLabel"></p>
                        <p class="text-xs text-gray-400 mt-1 hidden" id="horoscopePromptAdminModalNote"></p>
                    </div>
                    <button type="button"
                            class="rounded-md text-gray-400 hover:text-gray-600"
                            data-prompt-modal-close
                            aria-label="{{ __('horoscope.js.element_info_close') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="hidden rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800" id="horoscopePromptAdminModalError"></div>
                <div class="hidden rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-800" id="horoscopePromptAdminModalSuccess"></div>

                <div class="text-center text-sm text-gray-500 hidden" id="horoscopePromptAdminModalLoading">{{ __('horoscope.js.element_info_loading') }}</div>

                <div id="horoscopePromptAdminModalContent" class="hidden space-y-4">
                    <div>
                        <label for="horoscopePromptAdminModalSystem" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('horoscope.prompt_preview_system') }}
                        </label>
                        <textarea id="horoscopePromptAdminModalSystem"
                                  rows="10"
                                  readonly
                                  class="block w-full rounded-md border-gray-300 bg-gray-50 text-xs font-mono shadow-sm"></textarea>
                    </div>

                    <div>
                        <label for="horoscopePromptAdminModalUser" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('horoscope.prompt_preview_user') }}
                        </label>
                        <textarea id="horoscopePromptAdminModalUser"
                                  rows="12"
                                  readonly
                                  class="block w-full rounded-md border-gray-300 bg-gray-50 text-xs font-mono shadow-sm"></textarea>
                    </div>

                    <div>
                        <label for="horoscopePromptAdminModalInstructions" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('horoscope.prompt_preview_instructions') }}
                        </label>
                        <p class="text-xs text-gray-500 mb-2">{{ __('horoscope.prompt_preview_instructions_hint') }}</p>
                        <textarea id="horoscopePromptAdminModalInstructions"
                                  rows="8"
                                  class="block w-full rounded-md border-gray-300 text-sm font-mono shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                <button type="button"
                        id="horoscopePromptAdminModalSave"
                        class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">
                    {{ __('horoscope.prompt_settings_save') }}
                </button>
                <button type="button"
                        id="horoscopePromptAdminModalReset"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    {{ __('horoscope.prompt_settings_reset') }}
                </button>
                <button type="button"
                        data-prompt-modal-close
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    {{ __('horoscope.js.element_info_close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
