<?php

namespace PeterMarkley\Tollerus\Enums;

enum NeographySectionType: string
{
    case Alphabet = 'alphabet';
    case Numerals = 'numerals';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

