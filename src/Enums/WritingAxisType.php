<?php

namespace PeterMarkley\Tollerus\Enums;

enum WritingAxisType: string
{
    case Horizontal = 'horiz';
    case Vertical   = 'vert';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function perpendicular(): self
    {
        return match ($this) {
            self::Horizontal => self::Vertical,
            self::Vertical => self::Horizontal,
        };
    }

    /** @return WritingDirection[] */
    public function directions(): array
    {
        return match ($this) {
            self::Horizontal => [
                WritingDirection::LeftToRight,
                WritingDirection::RightToLeft,
            ],
            self::Vertical => [
                WritingDirection::TopToBottom,
                WritingDirection::BottomToTop,
            ],
        };
    }
}
