<?php

namespace App\Services;

use App\Enums\AspectType;
use App\Enums\AstrologyObject;
use App\Enums\ZodiacSign;
use App\Models\BirthChart;
use App\Models\ChartAspect;
use App\Models\ChartPlacement;
use App\Models\ChartScore;
use App\Models\NatalChart;
use App\Models\ScoringProfile;
use App\Models\User;
use App\Models\UserHoroscope;
use Illuminate\Support\Facades\DB;

class AstrologyChartScoringService
{
    public function __construct(
        private readonly HoroscopeCalculator $calculator,
        private readonly ChartScoringCalculator $scoringCalculator,
        private readonly AstroMottoChartScoringCalculator $astroMottoCalculator,
    ) {}

    public function scoreBirthChart(BirthChart $birthChart, ?User $user = null): ChartScore
    {
        $user ??= $birthChart->user;
        if (! $user) {
            throw new \RuntimeException('A születési adathoz nincs felhasználó.');
        }

        $payload = $this->buildPayloadFromBirthChart($birthChart, $user);
        $calculated = $this->calculator->calculate($payload);
        $natal = (array) ($calculated['natal'] ?? []);

        $natalChart = $this->persistChart(
            user: $user,
            natalData: $natal,
            sidereal: (bool) ($calculated['sidereal'] ?? false),
            ayanamsa: $calculated['ayanamsa'] ?? null,
            houseSystem: (string) ($calculated['house_system'] ?? $user->house_system ?? 'placidus'),
            birthChartId: $birthChart->id,
        );

        return $this->scoreNatalChart($natalChart);
    }

    /**
     * @param  array<string, mixed>  $calculatedData  Teljes python válasz vagy {natal: ...}
     */
    public function scoreFromCalculatedData(User $user, array $calculatedData, ?int $birthChartId = null): ?ChartScore
    {
        $natal = (array) ($calculatedData['natal'] ?? $calculatedData);
        if ($natal === [] || ! isset($natal['planets'])) {
            return null;
        }

        $natalChart = $this->persistChart(
            user: $user,
            natalData: $natal,
            sidereal: (bool) ($calculatedData['sidereal'] ?? ($user->zodiac_mode === 'sidereal')),
            ayanamsa: $calculatedData['ayanamsa'] ?? null,
            houseSystem: (string) ($calculatedData['house_system'] ?? $user->house_system ?? 'placidus'),
            birthChartId: $birthChartId,
        );

        return $this->scoreNatalChart($natalChart, $user);
    }

    public function scoreUserHoroscope(UserHoroscope $horoscope): ChartScore
    {
        $data = (array) ($horoscope->data ?? []);
        $natal = [
            'datetime_utc' => $horoscope->calculated_at?->utc()->toIso8601String(),
            'asc' => $data['asc'] ?? null,
            'mc' => $data['mc'] ?? null,
            'houses' => $data['houses'] ?? [],
            'planets' => $data['planets'] ?? [],
            'aspects' => $data['aspects'] ?? [],
        ];

        $natalChart = $this->persistChart(
            user: $horoscope->user,
            natalData: $natal,
            sidereal: (bool) $horoscope->sidereal,
            ayanamsa: $horoscope->ayanamsa,
            houseSystem: (string) $horoscope->house_system,
            userHoroscopeId: $horoscope->id,
        );

        return $this->scoreNatalChart($natalChart, $horoscope->user);
    }

    /**
     * @return list<ChartScore>
     */
    public function scoreAllProfiles(NatalChart $natalChart, ?User $user = null): array
    {
        $scores = [];
        foreach (ScoringProfile::query()->orderBy('id')->get() as $profile) {
            $scores[] = $this->scoreWithProfile($natalChart, $profile);
        }

        return $scores;
    }

    public function scoreNatalChart(NatalChart $natalChart, ?User $user = null): ChartScore
    {
        $user ??= $natalChart->user;
        $this->scoreAllProfiles($natalChart, $user);

        return $this->findScoreForNatalChart($natalChart, $user)
            ?? throw new \RuntimeException('Nem sikerült pontozni a képletet.');
    }

    private function scoreWithProfile(NatalChart $natalChart, ScoringProfile $profile): ChartScore
    {
        $result = $profile->isAstroMotto()
            ? $this->astroMottoCalculator->calculate($natalChart, $profile)
            : $this->scoringCalculator->calculate($natalChart, $profile);

        return ChartScore::updateOrCreate(
            [
                'natal_chart_id' => $natalChart->id,
                'scoring_profile_id' => $profile->id,
            ],
            [
                ...$result,
                'calculated_at' => now(),
            ]
        );
    }

    public function findScoreForNatalChart(NatalChart $natalChart, ?User $user = null): ?ChartScore
    {
        $profile = ScoringProfile::forUser($user ?? $natalChart->user);

        if (! $profile) {
            return $natalChart->latestScore;
        }

        return ChartScore::query()
            ->where('natal_chart_id', $natalChart->id)
            ->where('scoring_profile_id', $profile->id)
            ->first();
    }

    public function findScoreForBirthChart(?int $birthChartId, ?User $user = null): ?ChartScore
    {
        if (! $birthChartId) {
            return null;
        }

        $natalChart = NatalChart::query()
            ->where('birth_chart_id', $birthChartId)
            ->first();

        if (! $natalChart) {
            return null;
        }

        return $this->findScoreForNatalChart($natalChart, $user);
    }

    /**
     * Pontozás mentés nélkül (pl. napi globális horoszkóp).
     *
     * @param  array<string, mixed>  $calculatedData
     * @return array<string, mixed>
     */
    public function scorePayload(array $calculatedData, ?User $user = null): array
    {
        $profile = ScoringProfile::forUser($user);
        if (! $profile) {
            return [];
        }

        return $this->scorePayloadForProfile($calculatedData, $profile);
    }

    /**
     * Mindkét (összes) pontozási profil kimenete mentés nélkül.
     *
     * @param  array<string, mixed>  $calculatedData
     * @return array<int, array<string, mixed>>
     */
    public function scoreAllPayloads(array $calculatedData): array
    {
        $payloads = [];

        foreach (ScoringProfile::query()->orderBy('id')->get() as $profile) {
            $payloads[$profile->id] = $this->scorePayloadForProfile($calculatedData, $profile);
        }

        return $payloads;
    }

    /**
     * @param  array<string, mixed>  $calculatedData
     * @return array<string, mixed>
     */
    public function scorePayloadForProfile(array $calculatedData, ScoringProfile $profile): array
    {
        $natal = (array) ($calculatedData['natal'] ?? $calculatedData);
        $natalChart = $this->buildEphemeralNatalChart(
            $natal,
            (bool) ($calculatedData['sidereal'] ?? false),
            (string) ($calculatedData['house_system'] ?? 'placidus'),
            $calculatedData['ayanamsa'] ?? null,
        );

        $result = $profile->isAstroMotto()
            ? $this->astroMottoCalculator->calculate($natalChart, $profile)
            : $this->scoringCalculator->calculate($natalChart, $profile);

        return array_merge($result, [
            'profile' => $profile->name,
            'engine' => $profile->engine,
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $natalData
     */
    public function buildEphemeralNatalChart(
        array $natalData,
        bool $sidereal,
        string $houseSystem,
        ?string $ayanamsa,
    ): NatalChart {
        $natalChart = new NatalChart([
            'datetime_utc' => $natalData['datetime_utc'] ?? now()->utc(),
            'lat' => (float) ($natalData['lat'] ?? 0),
            'lon' => (float) ($natalData['lon'] ?? 0),
            'sidereal' => $sidereal,
            'ayanamsa' => $ayanamsa,
            'house_system' => $houseSystem,
        ]);

        $placements = [];
        foreach ((array) ($natalData['planets'] ?? []) as $planet) {
            $object = AstrologyObject::tryFromName((string) ($planet['name'] ?? ''));
            $sign = ZodiacSign::tryFromName((string) ($planet['sign'] ?? ''));
            if (! $object || ! $sign) {
                continue;
            }
            $placements[] = new ChartPlacement([
                'object' => $object->value,
                'sign' => $sign->value,
                'house' => (int) ($planet['house'] ?? 1),
                'longitude' => (float) ($planet['longitude'] ?? 0),
                'sign_degree' => (float) ($planet['sign_degree'] ?? 0),
            ]);
        }

        if (isset($natalData['asc'])) {
            $asc = (float) $natalData['asc'];
            $sign = $this->signFromLongitude($asc);
            if ($sign) {
                $placements[] = new ChartPlacement([
                    'object' => AstrologyObject::Asc->value,
                    'sign' => $sign->value,
                    'house' => 1,
                    'longitude' => $asc,
                    'sign_degree' => fmod($asc, 30),
                ]);
            }
        }

        if (isset($natalData['mc'])) {
            $mc = (float) $natalData['mc'];
            $sign = $this->signFromLongitude($mc);
            if ($sign) {
                $placements[] = new ChartPlacement([
                    'object' => AstrologyObject::Mc->value,
                    'sign' => $sign->value,
                    'house' => 10,
                    'longitude' => $mc,
                    'sign_degree' => fmod($mc, 30),
                ]);
            }
        }

        $aspects = [];
        foreach ((array) ($natalData['aspects'] ?? []) as $aspect) {
            $body1 = AstrologyObject::tryFromName((string) ($aspect['p1'] ?? ''));
            $body2 = AstrologyObject::tryFromName((string) ($aspect['p2'] ?? ''));
            $type = AspectType::tryFromName((string) ($aspect['type'] ?? ''));
            if (! $body1 || ! $body2 || ! $type) {
                continue;
            }
            $orb = (float) ($aspect['orb'] ?? 0);
            $aspects[] = new ChartAspect([
                'body1' => $body1->value,
                'body2' => $body2->value,
                'aspect_type' => $type->value,
                'orb' => $orb,
                'strength' => max(0, 1 - ($orb / 8)),
            ]);
        }

        $natalChart->setRelation('placements', collect($placements));
        $natalChart->setRelation('aspects', collect($aspects));

        return $natalChart;
    }

    /**
     * @param  array<string, mixed>  $natalData
     */
    private function persistChart(
        User $user,
        array $natalData,
        bool $sidereal,
        ?string $ayanamsa,
        string $houseSystem,
        ?int $birthChartId = null,
        ?int $userHoroscopeId = null,
    ): NatalChart {
        return DB::transaction(function () use ($user, $natalData, $sidereal, $ayanamsa, $houseSystem, $birthChartId, $userHoroscopeId) {
            $natalChart = $this->resolveNatalChartRecord(
                $user,
                $natalData,
                $sidereal,
                $ayanamsa,
                $houseSystem,
                $birthChartId,
                $userHoroscopeId,
            );

            $natalChart->placements()->delete();
            $natalChart->aspects()->delete();

            $this->syncPlacements($natalChart, $natalData);
            $this->syncAspects($natalChart, $natalData);

            return $natalChart->fresh(['placements', 'aspects']);
        });
    }

    /**
     * @param  array<string, mixed>  $natalData
     */
    private function resolveNatalChartRecord(
        User $user,
        array $natalData,
        bool $sidereal,
        ?string $ayanamsa,
        string $houseSystem,
        ?int $birthChartId,
        ?int $userHoroscopeId,
    ): NatalChart {
        $attributes = [
            'user_id' => $user->id,
            'datetime_utc' => $natalData['datetime_utc'] ?? now()->utc(),
            'lat' => (float) ($natalData['lat'] ?? 0),
            'lon' => (float) ($natalData['lon'] ?? 0),
            'sidereal' => $sidereal,
            'ayanamsa' => $ayanamsa,
            'house_system' => $houseSystem,
            'birth_chart_id' => $birthChartId,
            'user_horoscope_id' => $userHoroscopeId,
        ];

        if ($birthChartId) {
            return NatalChart::updateOrCreate(
                ['birth_chart_id' => $birthChartId],
                $attributes,
            );
        }

        if ($userHoroscopeId) {
            return NatalChart::updateOrCreate(
                ['user_horoscope_id' => $userHoroscopeId],
                $attributes,
            );
        }

        return NatalChart::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $natalData
     */
    private function syncPlacements(NatalChart $natalChart, array $natalData): void
    {
        $rows = [];

        foreach ((array) ($natalData['planets'] ?? []) as $planet) {
            $object = AstrologyObject::tryFromName((string) ($planet['name'] ?? ''));
            $sign = ZodiacSign::tryFromName((string) ($planet['sign'] ?? ''));

            if (! $object || ! $sign) {
                continue;
            }

            $rows[] = [
                'natal_chart_id' => $natalChart->id,
                'object' => $object->value,
                'sign' => $sign->value,
                'house' => (int) ($planet['house'] ?? 1),
                'longitude' => (float) ($planet['longitude'] ?? 0),
                'sign_degree' => (float) ($planet['sign_degree'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $asc = isset($natalData['asc']) ? (float) $natalData['asc'] : null;
        $mc = isset($natalData['mc']) ? (float) $natalData['mc'] : null;

        if ($asc !== null) {
            $sign = $this->signFromLongitude($asc);
            if ($sign) {
                $rows[] = $this->anglePlacementRow($natalChart->id, AstrologyObject::Asc, $asc, $sign, $natalData);
            }
        }

        if ($mc !== null) {
            $sign = $this->signFromLongitude($mc);
            if ($sign) {
                $rows[] = $this->anglePlacementRow($natalChart->id, AstrologyObject::Mc, $mc, $sign, $natalData, house: 10);
            }
        }

        if ($rows !== []) {
            ChartPlacement::insert($rows);
        }
    }

    /**
     * @param  array<string, mixed>  $natalData
     * @return array<string, mixed>
     */
    private function anglePlacementRow(
        int $natalChartId,
        AstrologyObject $object,
        float $longitude,
        ZodiacSign $sign,
        array $natalData,
        int $house = 1,
    ): array {
        if ($object === AstrologyObject::Asc) {
            $house = 1;
        }

        return [
            'natal_chart_id' => $natalChartId,
            'object' => $object->value,
            'sign' => $sign->value,
            'house' => $house,
            'longitude' => $longitude,
            'sign_degree' => fmod($longitude, 30),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $natalData
     */
    private function syncAspects(NatalChart $natalChart, array $natalData): void
    {
        $rows = [];

        foreach ((array) ($natalData['aspects'] ?? []) as $aspect) {
            $body1 = AstrologyObject::tryFromName((string) ($aspect['p1'] ?? ''));
            $body2 = AstrologyObject::tryFromName((string) ($aspect['p2'] ?? ''));
            $type = AspectType::tryFromName((string) ($aspect['type'] ?? ''));

            if (! $body1 || ! $body2 || ! $type) {
                continue;
            }

            $orb = (float) ($aspect['orb'] ?? 0);
            $rows[] = [
                'natal_chart_id' => $natalChart->id,
                'body1' => $body1->value,
                'body2' => $body2->value,
                'aspect_type' => $type->value,
                'orb' => $orb,
                'strength' => max(0, 1 - ($orb / 8)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            ChartAspect::insert($rows);
        }
    }

    private function signFromLongitude(float $longitude): ?ZodiacSign
    {
        $signs = [
            'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
            'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces',
        ];
        $index = (int) floor(fmod($longitude, 360) / 30);

        return ZodiacSign::tryFromName($signs[$index] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayloadFromBirthChart(BirthChart $birthChart, User $user): array
    {
        if (! $birthChart->birth_datetime_utc || $birthChart->birth_lat === null || $birthChart->birth_lon === null) {
            throw new \RuntimeException('Hiányos születési adat – nem értékelhető a képlet.');
        }

        $sidereal = ($user->zodiac_mode ?? 'tropical') === 'sidereal';
        $entry = [
            'datetime_utc' => $birthChart->birth_datetime_utc->utc()->toIso8601String(),
            'lat' => (float) $birthChart->birth_lat,
            'lon' => (float) $birthChart->birth_lon,
        ];

        return [
            'natal' => $entry,
            'transit' => $entry,
            'sidereal' => $sidereal,
            'ayanamsa' => $sidereal ? 'lahiri' : 'lahiri',
            'house_system' => (string) ($user->house_system ?? 'placidus'),
        ];
    }
}
