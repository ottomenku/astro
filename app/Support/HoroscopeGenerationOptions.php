<?php

namespace App\Support;

class HoroscopeGenerationOptions
{
    public const DETAIL_SHORT = 'short';

    public const DETAIL_NORMAL = 'normal';

    public const DETAIL_DETAILED = 'detailed';

    /**
     * @param  list<string>  $topics
     */
    public function __construct(
        public ?string $userFocus = null,
        public string $detailLevel = self::DETAIL_NORMAL,
        public array $topics = [],
    ) {
        $this->topics = HoroscopeTopicCatalog::normalizeTopics($this->topics);
    }

    /**
     * @param  list<string>|null  $topics
     */
    public static function fromRequest(?string $userFocus, ?string $detailLevel, ?array $topics = null): self
    {
        $level = in_array($detailLevel, [self::DETAIL_SHORT, self::DETAIL_NORMAL, self::DETAIL_DETAILED], true)
            ? $detailLevel
            : self::DETAIL_NORMAL;

        $focus = trim((string) $userFocus);

        return new self(
            userFocus: $focus !== '' ? $focus : null,
            detailLevel: $level,
            topics: HoroscopeTopicCatalog::normalizeTopics($topics),
        );
    }

    /**
     * @return list<string>
     */
    public function normalizedTopics(): array
    {
        return HoroscopeTopicCatalog::normalizeTopics($this->topics);
    }

    public function isDefault(): bool
    {
        return $this->userFocus === null
            && $this->detailLevel === self::DETAIL_NORMAL
            && $this->normalizedTopics() === HoroscopeTopicCatalog::ALL;
    }

    public function cacheSuffix(): string
    {
        if ($this->isDefault()) {
            return '';
        }

        $focusHash = substr(md5($this->userFocus ?? ''), 0, 12);
        $topicsKey = implode('-', $this->normalizedTopics());

        return ':lvl:'.$this->detailLevel.':focus:'.$focusHash.':topics:'.$topicsKey;
    }
}
