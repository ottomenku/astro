<x-app-layout>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('partials.app-icon-toolbar')

                <div class="max-w-2xl mt-6">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">{{ __('app.profile_daily_horoscope') }}</h2>
                            <p class="mt-1 text-sm text-gray-600">{{ __('app.profile_daily_horoscope_hint') }}</p>
                        </header>

                        <form method="post" action="{{ route('profile.daily-horoscope.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="flex items-center gap-2">
                                <input id="use_personal_daily" name="use_personal_daily" value="1" type="checkbox"
                                       class="rounded border-gray-300"
                                       @checked(old('use_personal_daily', $settings->use_personal_daily))>
                                <label for="use_personal_daily" class="text-sm text-gray-700">{{ __('app.use_personal_daily') }}</label>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('app.use_personal_daily_hint') }}</p>

                            <div>
                                <x-input-label for="scoring_profile_id" :value="__('app.daily_scoring_profile')" />
                                <select id="scoring_profile_id" name="scoring_profile_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">{{ __('app.daily_scoring_profile_default') }}</option>
                                    @foreach ($scoringProfiles as $profile)
                                        <option value="{{ $profile->id }}"
                                            @selected((int) old('scoring_profile_id', $settings->scoring_profile_id) === (int) $profile->id)>
                                            {{ $profile->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="attached_source" :value="__('app.daily_attached_chart')" />
                                @php
                                    $attachedSource = old('attached_source', $settings->birth_chart_id ? 'birth_chart' : ($settings->user_horoscope_id ? 'user_horoscope' : 'none'));
                                @endphp
                                <select id="attached_source" name="attached_source"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="none" @selected($attachedSource === 'none')>{{ __('app.daily_attached_none') }}</option>
                                    <option value="birth_chart" @selected($attachedSource === 'birth_chart')>{{ __('app.daily_attached_birth_chart') }}</option>
                                    <option value="user_horoscope" @selected($attachedSource === 'user_horoscope')>{{ __('app.daily_attached_saved_horoscope') }}</option>
                                </select>
                            </div>

                            <div id="birthChartPicker" class="{{ $attachedSource === 'birth_chart' ? '' : 'hidden' }}">
                                <x-input-label for="birth_chart_id" :value="__('app.profile_birth_charts')" />
                                <select id="birth_chart_id" name="birth_chart_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach ($birthCharts as $chart)
                                        <option value="{{ $chart->id }}"
                                            @selected((int) old('birth_chart_id', $settings->birth_chart_id) === (int) $chart->id)>
                                            {{ $chart->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="horoscopePicker" class="{{ $attachedSource === 'user_horoscope' ? '' : 'hidden' }}">
                                <x-input-label for="user_horoscope_id" :value="__('app.daily_saved_horoscopes')" />
                                <select id="user_horoscope_id" name="user_horoscope_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach ($savedHoroscopes as $horoscope)
                                        <option value="{{ $horoscope->id }}"
                                            @selected((int) old('user_horoscope_id', $settings->user_horoscope_id) === (int) $horoscope->id)>
                                            {{ $horoscope->label ?: ('#'.$horoscope->id) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="system_prompt" :value="__('app.daily_system_prompt')" />
                                <textarea id="system_prompt" name="system_prompt" rows="6"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm"
                                          placeholder="{{ __('app.daily_prompt_placeholder') }}">{{ old('system_prompt', $settings->system_prompt) }}</textarea>
                            </div>

                            <div>
                                <x-input-label for="user_prompt_template" :value="__('app.daily_user_prompt')" />
                                <textarea id="user_prompt_template" name="user_prompt_template" rows="8"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm"
                                          placeholder="{{ __('app.daily_prompt_placeholder') }}">{{ old('user_prompt_template', $settings->user_prompt_template) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ __('app.daily_prompt_placeholders') }}</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('status') === 'daily-horoscope-updated')
                                    <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        const source = document.getElementById('attached_source');
        const birthPicker = document.getElementById('birthChartPicker');
        const horoscopePicker = document.getElementById('horoscopePicker');

        source?.addEventListener('change', () => {
            birthPicker.classList.toggle('hidden', source.value !== 'birth_chart');
            horoscopePicker.classList.toggle('hidden', source.value !== 'user_horoscope');
        });
    </script>
</x-app-layout>
