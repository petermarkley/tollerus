<?php

namespace PeterMarkley\Tollerus\Enums;

enum MorphRulePatternType: string
{
    case Transliterated = 'transliterated';
    case Phonemic = 'phonemic';
    case Native = 'native';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function localize(): string
    {
        return match ($this) {
            self::Transliterated => config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated')),
            self::Phonemic       => __('tollerus::ui.phonemic'),
            self::Native         => __('tollerus::ui.native'),
        };
    }
}