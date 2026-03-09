<?php

namespace PeterMarkley\Tollerus\Enums;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Enum → model class mapping.
     *
     * @return class-string<Model>|null
     */
    public function model(): ?string
    {
        return match ($this) {
            self::Glyph  => \PeterMarkley\Tollerus\Models\NeographyGlyph::class,
            self::Entry  => \PeterMarkley\Tollerus\Models\Entry::class,
            self::Lexeme => \PeterMarkley\Tollerus\Models\Lexeme::class,
            self::Form   => \PeterMarkley\Tollerus\Models\Form::class,
        };
    }
}

