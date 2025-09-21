<?php

namespace PeterMarkley\Tollerus\Enums;

enum NeographyGlyphType: string
{
    case Symbol = 'symbol';
    case Mark = 'mark';
    case Numeral = 'numeral';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

