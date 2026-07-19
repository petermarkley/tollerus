<?php

use PeterMarkley\Tollerus\Support\Markup\BodyTextRenderer;

/**
 * FIXME - These tests aren't very good because they don't create
 * database conditions that allow BodyTextRenderer to properly
 * validate the objects or populate missing data attributes, etc.
 *
 * Without that, `render($inputHtml, true)` is basically a no-op.
 */
it('correctly renders HTML for no-hyperlink display', function () {
    $sanitizer = new BodyTextRenderer;
    $inputHtml = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word: <span data-tollerus="word" data-id="AAR3" data-lang="myconlang">shibboleth</span></p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="1" data-neography="myneography" class="tollerus_custom_myneography">󲰀󲰁󲰂󲰃</span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈʃɪbəlɛθ</span></p>
        <p>Here is a hyperlink: <a href="https://example.com">click me</a></p>
    </div>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word: shibboleth</p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="1" data-neography="myneography" class="tollerus_custom_myneography">󲰀󲰁󲰂󲰃</span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈʃɪbəlɛθ</span></p>
        <p>Here is a hyperlink: click me</p>
    </div>
    HTM;
    $outputHtmlRaw = $sanitizer->render($inputHtml, false);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
it('correctly renders HTML for default display', function () {
    $sanitizer = new BodyTextRenderer;
    $inputHtml = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word: <span data-tollerus="word" data-id="AAR3" data-lang="myconlang">shibboleth</span></p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="1" data-neography="myneography" class="tollerus_custom_myneography">󲰀󲰁󲰂󲰃</span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈʃɪbəlɛθ</span></p>
        <p>Here is a hyperlink: <a href="https://example.com">click me</a></p>
    </div>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word: <span data-tollerus="word" data-id="AAR3" data-lang="myconlang">shibboleth</span></p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="1" data-neography="myneography" class="tollerus_custom_myneography">󲰀󲰁󲰂󲰃</span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈʃɪbəlɛθ</span></p>
        <p>Here is a hyperlink: <a href="https://example.com">click me</a></p>
    </div>
    HTM;
    $outputHtmlRaw = $sanitizer->render($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
