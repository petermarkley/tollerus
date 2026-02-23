<?php

namespace PeterMarkley\Tollerus\Support\Markup;

use Masterminds\HTML5;

class BodyTextRenderer
{
    public function render(string $html): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTMLFragment($html);

        return $html5->saveHTML($dom);
    }
}