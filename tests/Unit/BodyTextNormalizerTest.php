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
it('correctly normalizes inline HTML for storage', function () {
    $normalizer = new BodyTextNormalizer;
    $inputHtml = "<p>Lorem ipsum dolor sit amet.</p>";
    $expectedHtmlRaw = "Lorem ipsum dolor sit amet.";
    $outputHtmlRaw = $normalizer->normalizeInlineForSave($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
it('correctly normalizes inline HTML for editing', function () {
    $normalizer = new BodyTextNormalizer;
    $inputHtml = "Lorem ipsum dolor sit amet.";
    $expectedHtmlRaw = "<p>Lorem ipsum dolor sit amet.</p>";
    $outputHtmlRaw = $normalizer->normalizeInlineForWysiwyg($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
