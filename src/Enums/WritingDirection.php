<?php

namespace PeterMarkley\Tollerus\Enums;

enum WritingDirection: string
{
    case LeftToRight = 'ltr'; // like most Western and Romance languages
    case RightToLeft = 'rtl'; // like Hebrew/Arabic
    case TopToBottom = 'ttb'; // like Chinese
    case BottomToTop = 'btt'; // (this one is extremely rare)

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function axis(): WritingAxisType
    {
        return match ($this) {
            self::LeftToRight => WritingAxisType::Horizontal,
            self::RightToLeft => WritingAxisType::Horizontal,
            self::TopToBottom => WritingAxisType::Vertical,
            self::BottomToTop => WritingAxisType::Vertical,
        };
    }
}
