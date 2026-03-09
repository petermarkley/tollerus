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

    public function localize(): string
    {
        return match ($this) {
            self::Symbol => __('tollerus::ui.symbol'),
            self::Mark => __('tollerus::ui.mark'),
            self::Numeral => __('tollerus::ui.numeral'),
        };
    }
}

