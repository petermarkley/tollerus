<?php

namespace PeterMarkley\Tollerus\Enums;

enum NeographySectionType: string
{
    case Alphabet   = 'alphabet';
    case Abugida    = 'abugida';
    case Syllabary  = 'syllabary';
    case Logography = 'logography';
    case Numerals   = 'numerals';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function localize(): string
    {
        return match ($this) {
            self::Alphabet   => __('tollerus::ui.alphabet'),
            self::Abugida    => __('tollerus::ui.abugida'),
            self::Syllabary  => __('tollerus::ui.syllabary'),
            self::Logography => __('tollerus::ui.logography'),
            self::Numerals   => __('tollerus::ui.numerals'),
        };
    }
}

