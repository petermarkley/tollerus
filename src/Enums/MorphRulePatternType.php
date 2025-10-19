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
}