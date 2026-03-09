<?php

namespace PeterMarkley\Tollerus\Enums;

enum SearchType: string
{
    case Transliterated = 'transliterated';
    case Native = 'native';
    case Definition = 'definition';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function localize(): string
    {
        return match ($this) {
            self::Transliterated => config('tollerus.local_transliteration_word', __('tollerus::ui.transliterated')),
            self::Native         => __('tollerus::ui.native'),
            self::Definition     => __('tollerus::ui.definition'),
        };
    }
}