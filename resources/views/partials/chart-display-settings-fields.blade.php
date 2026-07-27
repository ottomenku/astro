@php
    use App\Support\ChartDisplaySettings;

    $chartDisplay = $chartDisplay ?? ChartDisplaySettings::defaults();
    $namedColors = ChartDisplaySettings::NAMED_COLORS;
    $inputName = $inputName ?? 'chart_display';
    $hint = $hint ?? null;
@endphp

<div class="space-y-6">
    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    <div>
        <h3 class="text-sm font-semibold text-gray-700">{{ __('horoscope.chart_display_aspects') }}</h3>

        <div class="mt-4 space-y-2">
            @foreach (ChartDisplaySettings::ASPECT_KEYS as $aspectKey)
                @php
                    $aspect = $chartDisplay['aspects'][$aspectKey];
                    $aspectLabel = __('horoscope.js.aspect_types.'.$aspectKey);
                @endphp
                <div class="grid grid-cols-[1fr_auto_auto] sm:grid-cols-[minmax(0,1fr)_5rem_9rem] gap-2 items-center">
                    <label class="flex items-center gap-2 text-sm text-gray-800">
                        <input type="hidden" name="{{ $inputName }}[aspects][{{ $aspectKey }}][enabled]" value="0">
                        <input
                            type="checkbox"
                            name="{{ $inputName }}[aspects][{{ $aspectKey }}][enabled]"
                            value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old("{$inputName}.aspects.{$aspectKey}.enabled", $aspect['enabled']))
                        >
                        <span>{{ $aspectLabel }}</span>
                    </label>
                    <span class="text-xs text-gray-500 hidden sm:inline">{{ __('horoscope.chart_display_color') }}</span>
                    <select
                        name="{{ $inputName }}[aspects][{{ $aspectKey }}][color]"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                    >
                        @foreach ($namedColors as $color)
                            <option value="{{ $color }}" @selected(old("{$inputName}.aspects.{$aspectKey}.color", $aspect['color']) === $color)>
                                {{ $color }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-input-error :messages="$errors->get("{$inputName}.aspects.{$aspectKey}.color")" class="mt-1" />
            @endforeach
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-gray-700">{{ __('horoscope.chart_display_objects') }}</h3>

        <div class="mt-4 space-y-2">
            @foreach (ChartDisplaySettings::OBJECT_KEYS as $objectKey)
                @php
                    $object = $chartDisplay['objects'][$objectKey];
                    $objectLabel = __('horoscope.js.planets.'.$objectKey);
                @endphp
                <div class="grid grid-cols-[1fr_auto_auto] sm:grid-cols-[minmax(0,1fr)_5rem_9rem] gap-2 items-center">
                    <label class="flex items-center gap-2 text-sm text-gray-800">
                        <input type="hidden" name="{{ $inputName }}[objects][{{ $objectKey }}][enabled]" value="0">
                        <input
                            type="checkbox"
                            name="{{ $inputName }}[objects][{{ $objectKey }}][enabled]"
                            value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old("{$inputName}.objects.{$objectKey}.enabled", $object['enabled']))
                        >
                        <span>{{ $objectLabel }}</span>
                    </label>
                    <span class="text-xs text-gray-500 hidden sm:inline">{{ __('horoscope.chart_display_color') }}</span>
                    <select
                        name="{{ $inputName }}[objects][{{ $objectKey }}][color]"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                    >
                        @foreach ($namedColors as $color)
                            <option value="{{ $color }}" @selected(old("{$inputName}.objects.{$objectKey}.color", $object['color']) === $color)>
                                {{ $color }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-input-error :messages="$errors->get("{$inputName}.objects.{$objectKey}.color")" class="mt-1" />
            @endforeach
        </div>
    </div>
</div>
