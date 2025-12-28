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

    public function localize(): string
    {
        return match ($this) {
            self::Alphabet => __('tollerus::ui.alphabet'),
            self::Numerals => __('tollerus::ui.numerals'),
        };
    }
}

