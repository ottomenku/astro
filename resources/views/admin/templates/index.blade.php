<x-app-layout>
    <div class="py-4">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 sm:p-8">
                @include('admin.partials.header')

                <header class="mb-6">
                    <h1 class="text-lg font-semibold text-gray-900">{{ __('app.admin_templates') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('app.admin_templates_intro') }}</p>
                </header>

                @if (session('status'))
                    <div class="mb-6 p-4 rounded bg-green-50 text-green-800 border border-green-200 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($templates as $key => $template)
                        @php
                            $isActive = $activeTemplate === $key;
                            $isAvailable = $template['available'] ?? false;
                            $previewFrom = $template['preview_from'] ?? '#111827';
                            $previewTo = $template['preview_to'] ?? '#374151';
                        @endphp

                        <article class="rounded-xl border {{ $isActive ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-200' }} overflow-hidden">
                            <div class="h-24" style="background: linear-gradient(135deg, {{ $previewFrom }}, {{ $previewTo }});"></div>

                            <div class="p-5 space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h2 class="font-semibold text-gray-900">{{ $template['name'] }}</h2>
                                        <p class="mt-1 text-sm text-gray-600">{{ $template['description'] }}</p>
                                    </div>

                                    @if ($isActive)
                                        <span class="shrink-0 rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                            {{ __('app.template_active_badge') }}
                                        </span>
                                    @elseif (! empty($template['coming_soon']))
                                        <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                            {{ __('app.template_coming_soon_badge') }}
                                        </span>
                                    @endif
                                </div>

                                @if ($isAvailable)
                                    <form method="post" action="{{ route('admin.templates.update') }}">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="template" value="{{ $key }}">
                                        <x-primary-button type="submit" :disabled="$isActive">
                                            {{ $isActive ? __('app.template_already_active') : __('app.template_activate') }}
                                        </x-primary-button>
                                    </form>
                                @else
                                    <button type="button" disabled class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-400 uppercase tracking-widest cursor-not-allowed">
                                        {{ __('app.template_coming_soon_badge') }}
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
