<x-app-layout>
    <div class="py-4" x-data="{ tab: @js($activeTab) }">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('admin.partials.header')

                <div class="flex flex-wrap items-center justify-between gap-3 mt-2 mb-4">
                    <div class="text-sm text-gray-600">
                        {{ $preview['forecast_date'] }} · {{ strtoupper($locale) }}
                    </div>
                    <div class="flex gap-2 text-sm">
                        <a href="{{ route('admin.daily-horoscope.edit', ['locale' => 'hu', 'tab' => $activeTab]) }}"
                           class="px-3 py-1 rounded border {{ $locale === 'hu' ? 'bg-indigo-50 border-indigo-300 font-semibold text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                            Magyar
                        </a>
                        <a href="{{ route('admin.daily-horoscope.edit', ['locale' => 'en', 'tab' => $activeTab]) }}"
                           class="px-3 py-1 rounded border {{ $locale === 'en' ? 'bg-indigo-50 border-indigo-300 font-semibold text-indigo-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                            English
                        </a>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="p-4 rounded bg-green-50 text-green-800 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded bg-red-50 text-red-800 border border-red-200 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm">
                    @if ($draft)
                        Státusz:
                        @if ($draft->isPublished())
                            <span class="font-medium text-green-700">Publikálva</span>
                            @if ($draft->published_at)
                                <span class="text-gray-500">· {{ $draft->published_at->format('Y.m.d H:i') }}</span>
                            @endif
                        @else
                            <span class="font-medium text-amber-700">Piszkozat</span>
                        @endif
                    @else
                        <span class="text-gray-500">Még nincs mai generált szöveg.</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500">Képlet UTC: {{ $preview['chart_datetime_utc'] }}</div>
            </div>

            {{-- Fülek --}}
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex flex-wrap gap-x-1 gap-y-1 -mb-px text-sm min-w-max">
                    <button type="button" @click="tab = 'generation'"
                            :class="tab === 'generation' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 border-b-2 whitespace-nowrap">Generálás</button>

                    <button type="button" @click="tab = 'response'"
                            :class="tab === 'response' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 border-b-2 whitespace-nowrap">Válasz</button>

                    <button type="button" @click="tab = 'prompt'"
                            :class="tab === 'prompt' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 border-b-2 whitespace-nowrap">Kimenő prompt</button>

                    @foreach ($profiles as $profile)
                        @php $scoreTab = 'score-'.$profile->id; @endphp
                        <button type="button" @click="tab = '{{ $scoreTab }}'"
                                :class="tab === '{{ $scoreTab }}' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-3 py-2 border-b-2 whitespace-nowrap">
                            {{ $profile->name }}
                            @if ((int) $setting->scoring_profile_id === (int) $profile->id)
                                <span class="text-indigo-400">●</span>
                            @endif
                        </button>
                    @endforeach

                    <button type="button" @click="tab = 'system'"
                            :class="tab === 'system' ? 'border-indigo-500 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-3 py-2 border-b-2 whitespace-nowrap">System prompt</button>
                </nav>
            </div>

            {{-- TAB: Generálás --}}
            <div x-show="tab === 'generation'" x-cloak>
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <form method="POST" action="{{ route('admin.daily-horoscope.generation.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="locale" value="{{ $locale }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="scoring_profile_id" value="Pontozás az LM-nek" />
                                <select id="scoring_profile_id" name="scoring_profile_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach ($profiles as $profile)
                                        <option value="{{ $profile->id }}" @selected((int) old('scoring_profile_id', $setting->scoring_profile_id) === (int) $profile->id)>
                                            {{ $profile->name }} ({{ $profile->engine }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input name="auto_publish" value="1" type="checkbox" class="rounded border-gray-300"
                                           @checked(old('auto_publish', $setting->auto_publish))>
                                    Automatikus publikálás
                                </label>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="user_prompt_append" value="Kiegészítés – milyen választ szeretnél?" />
                            <textarea id="user_prompt_append" name="user_prompt_append" rows="6"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                      placeholder="Pl.: Ma hangsúlyosabb, de realista hangvétel…">{{ old('user_prompt_append', $setting->user_prompt_append ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Mentés után a <strong>Kimenő prompt</strong> fül frissül.</p>
                        </div>

                        <div class="border-t border-gray-200 pt-4 space-y-4">
                            <h3 class="text-sm font-semibold text-gray-800">Horoszkóp generálás – mondatlimitok</h3>
                            <p class="text-xs text-gray-500">A felhasználói kifejtés és üzenet menük rövid / normál / részletes választója ezeket az értékeket használja.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <p class="text-xs font-medium text-gray-700 uppercase tracking-wide">Kifejtés</p>
                                    <div>
                                        <x-input-label for="explanation_sentences_short" value="Rövid (mondat)" />
                                        <x-text-input id="explanation_sentences_short" name="explanation_sentences_short" type="number" min="5" max="500"
                                                      class="mt-1 block w-full" :value="old('explanation_sentences_short', $setting->explanation_sentences_short ?? 20)" />
                                    </div>
                                    <div>
                                        <x-input-label for="explanation_sentences_normal" value="Normál (mondat)" />
                                        <x-text-input id="explanation_sentences_normal" name="explanation_sentences_normal" type="number" min="5" max="500"
                                                      class="mt-1 block w-full" :value="old('explanation_sentences_normal', $setting->explanation_sentences_normal ?? 50)" />
                                    </div>
                                    <div>
                                        <x-input-label for="explanation_sentences_detailed" value="Részletes (mondat)" />
                                        <x-text-input id="explanation_sentences_detailed" name="explanation_sentences_detailed" type="number" min="5" max="500"
                                                      class="mt-1 block w-full" :value="old('explanation_sentences_detailed', $setting->explanation_sentences_detailed ?? 100)" />
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <p class="text-xs font-medium text-gray-700 uppercase tracking-wide">Üzenet</p>
                                    <div>
                                        <x-input-label for="message_sentences_short" value="Rövid (mondat)" />
                                        <x-text-input id="message_sentences_short" name="message_sentences_short" type="number" min="5" max="500"
                                                      class="mt-1 block w-full" :value="old('message_sentences_short', $setting->message_sentences_short ?? 20)" />
                                    </div>
                                    <div>
                                        <x-input-label for="message_sentences_normal" value="Normál (mondat)" />
                                        <x-text-input id="message_sentences_normal" name="message_sentences_normal" type="number" min="5" max="500"
                                                      class="mt-1 block w-full" :value="old('message_sentences_normal', $setting->message_sentences_normal ?? 50)" />
                                    </div>
                                    <div>
                                        <x-input-label for="message_sentences_detailed" value="Részletes (mondat)" />
                                        <x-text-input id="message_sentences_detailed" name="message_sentences_detailed" type="number" min="5" max="500"
                                                      class="mt-1 block w-full" :value="old('message_sentences_detailed', $setting->message_sentences_detailed ?? 100)" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Beállítások mentése
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.daily-horoscope.regenerate') }}" class="pt-4 border-t">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $locale }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Frissítés (LM újragenerálás)
                        </button>
                        <span class="ml-2 text-xs text-gray-500">A mentett beállítások alapján generál.</span>
                    </form>
                </div>
            </div>

            {{-- TAB: Válasz --}}
            <div x-show="tab === 'response'" x-cloak>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    @if ($draft)
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-200">
                            <div class="text-sm">
                                @if ($draft->isPublished())
                                    <span class="font-medium text-green-700">Publikálva</span>
                                    @if ($draft->published_at)
                                        <span class="text-gray-500">· {{ $draft->published_at->format('Y.m.d H:i') }}</span>
                                    @endif
                                @else
                                    <span class="font-medium text-amber-700">Piszkozat – még nem látszik a nyitólapon</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" form="draftMessageForm"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Mentés
                                </button>

                                <form method="POST" action="{{ route('admin.daily-horoscope.publish') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $locale }}">
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        {{ $draft->isPublished() ? 'Újrapublikálás' : 'Publikálás' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <form id="draftMessageForm" method="POST" action="{{ route('admin.daily-horoscope.message.update') }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="locale" value="{{ $locale }}">

                            <div>
                                <x-input-label for="motto" value="Mottó" />
                                <x-text-input id="motto" name="motto" type="text" class="mt-1 block w-full"
                                              :value="old('motto', $draft->motto)" required />
                            </div>

                            @foreach (['summary' => 'Összefoglaló', 'health' => 'Egészség', 'money' => 'Pénz', 'relationships' => 'Párkapcsolat', 'work' => 'Munka'] as $field => $label)
                                <div>
                                    <x-input-label :for="$field" :value="$label" />
                                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" required
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old($field, $draft->$field) }}</textarea>
                                </div>
                            @endforeach
                        </form>
                    @else
                        <p class="text-sm text-gray-600">
                            Még nincs generált szöveg. A <strong>Generálás</strong> fülön kattints a Frissítés gombra.
                        </p>
                    @endif
                </div>
            </div>

            {{-- TAB: Kimenő prompt --}}
            <div x-show="tab === 'prompt'" x-cloak class="space-y-4">
                <p class="text-sm text-gray-600">
                    Ez megy az LM-nek (system + user). A <strong>Generálás → Beállítások mentése</strong> után frissül.
                    Aktív pontozás: <strong>{{ $setting->scoringProfile?->name ?? '—' }}</strong>
                </p>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">System</h3>
                        <pre class="p-4 bg-gray-50 border rounded text-xs whitespace-pre-wrap max-h-80 overflow-y-auto">{{ $assembledSystemPrompt }}</pre>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">User</h3>
                        <pre class="p-4 bg-gray-50 border rounded text-xs whitespace-pre-wrap max-h-[32rem] overflow-y-auto">{{ $assembledUserPrompt ?: '—' }}</pre>
                    </div>
                </div>
            </div>

            {{-- TAB: Pontozás profilonként --}}
            @foreach ($profiles as $profile)
                @php
                    $scoreTab = 'score-'.$profile->id;
                    $score = $scores[$profile->id] ?? [];
                    $isSelected = (int) $setting->scoring_profile_id === (int) $profile->id;
                @endphp
                <div x-show="tab === '{{ $scoreTab }}'" x-cloak x-data="{ showRawJson: false }">
                    <div class="bg-white shadow-sm sm:rounded-lg border {{ $isSelected ? 'border-indigo-400 ring-1 ring-indigo-200' : 'border-gray-200' }}">
                        <div class="px-4 py-3 bg-gray-50 border-b flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-medium">{{ $profile->name }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $profile->engine }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($isSelected)
                                    <span class="text-xs font-semibold text-indigo-600">Aktív az LM-nél</span>
                                @endif
                                <button type="button"
                                        @click="showRawJson = !showRawJson"
                                        class="inline-flex items-center px-2.5 py-1 rounded border border-gray-300 bg-white text-xs text-gray-700 hover:bg-gray-50"
                                        x-text="showRawJson ? 'Strukturált nézet' : 'Nyers JSON'">
                                </button>
                            </div>
                        </div>
                        <div x-show="!showRawJson">
                            @include('admin.daily-horoscope.partials.score-breakdown', [
                                'score' => $score,
                                'profile' => $profile,
                            ])
                        </div>
                        <div x-show="showRawJson" x-cloak class="p-4">
                            <pre class="p-4 bg-gray-50 border border-gray-200 rounded text-xs overflow-x-auto max-h-[36rem] overflow-y-auto whitespace-pre-wrap">{{ json_encode($score, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- TAB: System prompt --}}
            <div x-show="tab === 'system'" x-cloak class="space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form method="POST" action="{{ route('admin.daily-horoscope.system.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="locale" value="{{ $locale }}">

                        <div>
                            <x-input-label for="system_prompt" value="Asztrológiai utasítások (szerkeszthető)" />
                            <textarea id="system_prompt" name="system_prompt" rows="14"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm">{{ old('system_prompt', $setting->system_prompt ?? '') }}</textarea>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Mentés
                        </button>
                    </form>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">Automatikusan csatolt kimeneti formátum</h3>
                    <p class="text-xs text-slate-600 mb-3">Nem szerkeszthető – mindig hozzáfűződik a system prompthoz.</p>
                    <pre class="text-xs whitespace-pre-wrap bg-white border rounded p-4 max-h-64 overflow-y-auto">{{ $systemOutputFormat }}</pre>
                </div>
            </div>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
