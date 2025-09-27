<?php

namespace PeterMarkley\Tollerus\Enums;

enum GlobalIdKind: string
{
    case Glyph = 'glyph';
    case Entry = 'entry';
    case Lexeme = 'lexeme';
    case Form = 'form';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

