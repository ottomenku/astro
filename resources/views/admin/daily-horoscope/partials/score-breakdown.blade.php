@php
    $breakdown = (array) ($score['breakdown'] ?? []);
    $isAstroMotto = ($profile->engine ?? '') === \App\Models\ScoringProfile::ENGINE_ASTRO_MOTTO;
    $placements = (array) ($breakdown['placements'] ?? []);
    $aspects = (array) ($breakdown['aspects'] ?? []);
    $elementShares = (array) ($breakdown['element_shares'] ?? []);
    $modalityShares = (array) ($breakdown['modality_shares'] ?? []);

    $formatNum = static fn ($value, int $decimals = 2): string => is_numeric($value)
        ? number_format((float) $value, $decimals, ',', ' ')
        : '—';

    $formatShare = static fn ($value): string => is_numeric($value)
        ? $formatNum((float) $value * 100, 1).'%'
        : '—';
@endphp

<div class="p-4 space-y-6 text-sm text-gray-800">
    <section>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Összesítő</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <div class="text-xs text-gray-500">Értékelés</div>
                <div class="mt-1 text-base font-semibold">{{ $score['rating_label'] ?? '—' }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <div class="text-xs text-gray-500">{{ $isAstroMotto ? 'Aktivitás-index' : 'Összpont' }}</div>
                <div class="mt-1 text-base font-semibold">{{ $formatNum($score['total_score'] ?? null, 3) }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <div class="text-xs text-gray-500">Polaritás (+ / − / egyenleg)</div>
                <div class="mt-1 font-medium">
                    {{ $formatNum($score['polarity_positive'] ?? null, 3) }}
                    /
                    {{ $formatNum($score['polarity_negative'] ?? null, 3) }}
                    /
                    {{ $formatNum($score['polarity_balance'] ?? null, 3) }}
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <div class="text-xs text-gray-500">Motor</div>
                <div class="mt-1 font-medium">{{ $score['engine'] ?? $profile->engine }}</div>
            </div>
        </div>
    </section>

    <section>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Elemek és modalitások</h3>
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 p-3">
                <div class="text-xs font-medium text-gray-600 mb-2">Elemek (nyers súly)</div>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                    <div class="flex justify-between gap-2"><dt>Tűz</dt><dd class="font-mono">{{ $formatNum($score['element_fire'] ?? null, 3) }}</dd></div>
                    <div class="flex justify-between gap-2"><dt>Föld</dt><dd class="font-mono">{{ $formatNum($score['element_earth'] ?? null, 3) }}</dd></div>
                    <div class="flex justify-between gap-2"><dt>Levegő</dt><dd class="font-mono">{{ $formatNum($score['element_air'] ?? null, 3) }}</dd></div>
                    <div class="flex justify-between gap-2"><dt>Víz</dt><dd class="font-mono">{{ $formatNum($score['element_water'] ?? null, 3) }}</dd></div>
                </dl>
                @if ($elementShares !== [])
                    <div class="mt-3 text-xs text-gray-500">Arányok</div>
                    <dl class="mt-1 grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        @foreach (['fire' => 'Tűz', 'earth' => 'Föld', 'air' => 'Levegő', 'water' => 'Víz'] as $key => $label)
                            <div class="flex justify-between gap-2">
                                <dt>{{ $label }}</dt>
                                <dd class="font-mono">{{ $formatShare($elementShares[$key] ?? null) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
                @if (! empty($breakdown['element_classification']))
                    <p class="mt-2 text-xs text-indigo-700">{{ $breakdown['element_classification'] }}</p>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 p-3">
                <div class="text-xs font-medium text-gray-600 mb-2">Modalitások (nyers súly)</div>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                    <div class="flex justify-between gap-2"><dt>Kardinális</dt><dd class="font-mono">{{ $formatNum($score['modality_cardinal'] ?? null, 3) }}</dd></div>
                    <div class="flex justify-between gap-2"><dt>Fix</dt><dd class="font-mono">{{ $formatNum($score['modality_fixed'] ?? null, 3) }}</dd></div>
                    <div class="flex justify-between gap-2"><dt>Változó</dt><dd class="font-mono">{{ $formatNum($score['modality_mutable'] ?? null, 3) }}</dd></div>
                </dl>
                @if ($modalityShares !== [])
                    <div class="mt-3 text-xs text-gray-500">Arányok</div>
                    <dl class="mt-1 grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                        @foreach (['cardinal' => 'Kardinális', 'fixed' => 'Fix', 'mutable' => 'Változó'] as $key => $label)
                            <div class="flex justify-between gap-2">
                                <dt>{{ $label }}</dt>
                                <dd class="font-mono">{{ $formatShare($modalityShares[$key] ?? null) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
                @if (! empty($breakdown['modality_classification']))
                    <p class="mt-2 text-xs text-indigo-700">{{ $breakdown['modality_classification'] }}</p>
                @endif
            </div>
        </div>
    </section>

    @if ($isAstroMotto && (
        isset($breakdown['polarity_harmony'])
        || isset($breakdown['element_harmony'])
        || isset($breakdown['modality_harmony'])
    ))
        <section>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Harmónia-mutatók</h3>
            <dl class="grid gap-2 sm:grid-cols-3 text-xs">
                @if (isset($breakdown['polarity_harmony']))
                    <div class="rounded border border-gray-200 px-3 py-2 flex justify-between gap-2">
                        <dt>Polaritás harmónia</dt>
                        <dd class="font-mono">{{ $formatNum($breakdown['polarity_harmony'], 3) }}</dd>
                    </div>
                @endif
                @if (isset($breakdown['element_harmony']))
                    <div class="rounded border border-gray-200 px-3 py-2 flex justify-between gap-2">
                        <dt>Elem-harmónia</dt>
                        <dd class="font-mono">{{ $formatNum($breakdown['element_harmony'], 3) }}</dd>
                    </div>
                @endif
                @if (isset($breakdown['modality_harmony']))
                    <div class="rounded border border-gray-200 px-3 py-2 flex justify-between gap-2">
                        <dt>Modalitás-harmónia</dt>
                        <dd class="font-mono">{{ $formatNum($breakdown['modality_harmony'], 3) }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    @endif

    @if (! $isAstroMotto && (isset($breakdown['placement_total']) || isset($breakdown['aspect_total'])))
        <section>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Részösszegek</h3>
            <dl class="grid gap-2 sm:grid-cols-2 text-xs">
                <div class="rounded border border-gray-200 px-3 py-2 flex justify-between gap-2">
                    <dt>Pozíciók összege</dt>
                    <dd class="font-mono">{{ $formatNum($breakdown['placement_total'] ?? null, 2) }}</dd>
                </div>
                <div class="rounded border border-gray-200 px-3 py-2 flex justify-between gap-2">
                    <dt>Szögek összege</dt>
                    <dd class="font-mono">{{ $formatNum($breakdown['aspect_total'] ?? null, 2) }}</dd>
                </div>
            </dl>
        </section>
    @endif

    @if ($placements !== [])
        <section>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Bolygópozíciók ({{ count($placements) }})</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-3 py-2">Test</th>
                            <th class="px-3 py-2">Jegy</th>
                            @unless ($isAstroMotto)
                                <th class="px-3 py-2">Ház</th>
                            @endunless
                            <th class="px-3 py-2">Méltóság</th>
                            <th class="px-3 py-2 text-right">Pont</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($placements as $row)
                            <tr>
                                <td class="px-3 py-2 font-medium">{{ $row['object'] ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $row['sign'] ?? '—' }}</td>
                                @unless ($isAstroMotto)
                                    <td class="px-3 py-2">{{ $row['house'] ?? '—' }}</td>
                                @endunless
                                <td class="px-3 py-2">{{ $row['dignity'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ $formatNum($row['points'] ?? null, $isAstroMotto ? 4 : 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($aspects !== [])
        <section>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Fényszögek ({{ count($aspects) }})</h3>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-3 py-2">Test 1</th>
                            <th class="px-3 py-2">Test 2</th>
                            <th class="px-3 py-2">Típus</th>
                            <th class="px-3 py-2 text-right">Orb</th>
                            <th class="px-3 py-2 text-right">{{ $isAstroMotto ? 'Hozzájárulás' : 'Pont' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($aspects as $row)
                            <tr>
                                <td class="px-3 py-2 font-medium">{{ $row['body1'] ?? '—' }}</td>
                                <td class="px-3 py-2 font-medium">{{ $row['body2'] ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $row['type'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ $formatNum($row['orb'] ?? null, 3) }}</td>
                                <td class="px-3 py-2 text-right font-mono">
                                    {{ $formatNum($row['contribution'] ?? $row['points'] ?? null, $isAstroMotto ? 4 : 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($score === [])
        <p class="text-sm text-gray-500">Nincs pontozási adat ehhez a profilhoz.</p>
    @endif

    @if (! empty($breakdown['calculation_version']))
        <p class="text-xs text-gray-400">Számítás verzió: {{ $breakdown['calculation_version'] }}</p>
    @endif
</div>
