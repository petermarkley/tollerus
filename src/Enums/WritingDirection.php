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

    public function localize(): string
    {
        return match ($this) {
            self::LeftToRight => __('tollerus::ui.left_to_right'),
            self::RightToLeft => __('tollerus::ui.right_to_left'),
            self::TopToBottom => __('tollerus::ui.top_to_bottom'),
            self::BottomToTop => __('tollerus::ui.bottom_to_top'),
        };
    }
}
