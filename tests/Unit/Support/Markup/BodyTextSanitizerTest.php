<?php

use PeterMarkley\Tollerus\Support\Markup\BodyTextSanitizer;

it('correctly sanitizes arbitrary input HTML', function () {
    $sanitizer = new BodyTextSanitizer;
    $inputHtml = <<<HTM
    <div><p style="color:#f00;">Lorem ipsum <custom_tag>dolor</custom_tag> sit <a href="#" data-illegal-attr="foobar">amet</a>.</p></div>
    <div><script>alert("hello, world");</script></div>
    <div><table><tr><td>Table</td></tr></table></div>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <div><p>Lorem ipsum dolor sit <a href="#">amet</a>.</p></div>
    <div>alert("hello, world");</div>
    <div>Table</div>
    HTM;
    $outputHtmlRaw = $sanitizer->sanitize($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
