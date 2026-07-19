<?php

use PeterMarkley\Tollerus\Support\Markup\BodyTextNormalizer;

it('correctly normalizes HTML for storage', function () {
    $normalizer = new BodyTextNormalizer;
    $inputHtml = <<<HTM
    <p>Lorem ipsum.</p>
    <p>Dolor sit amet.</p>
    <p></p>
    <p>Consectetur adipiscing elit.</p>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <div>
        <p>Lorem ipsum.</p>
        <p>Dolor sit amet.</p>
    </div>
    <div>
        <p>Consectetur adipiscing elit.</p>
    </div>
    HTM;
    $outputHtmlRaw = $normalizer->normalizeForSave($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
it('correctly normalizes HTML for editing', function () {
    $normalizer = new BodyTextNormalizer;
    $inputHtml = <<<HTM
    <div>
        <p>Lorem ipsum.</p>
        <p>Dolor sit amet.</p>
    </div>
    <div>
        <p>Consectetur adipiscing elit.</p>
    </div>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <p>Lorem ipsum.</p>
    <p>Dolor sit amet.</p>
    <p></p>
    <p>Consectetur adipiscing elit.</p>
    HTM;
    $outputHtmlRaw = $normalizer->normalizeForWysiwyg($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
