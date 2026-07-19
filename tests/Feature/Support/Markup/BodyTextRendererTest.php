<?php

use PeterMarkley\Tollerus\Database\Seeders\FileImportSeeder;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Support\Markup\BodyTextRenderer;

/**
 * NOTE:
 * These tests are brittle with respect to the order of HTML attributes
 * within each tag. A fully correct approach would involve DOM traversal,
 * not string comparison. However at the moment, that's a bit overblown for
 * the size and stability of the code that we're testing. For now, string
 * comparison is adequate.
 */

it('correctly renders HTML for no-hyperlink display', function () {
    // Set up database context
    (new FileImportSeeder())->run();
    /**
     * After each test, the database changes are rolled back. This doesn't
     * necessarily reset the MySQL auto-increment counters, which means our
     * test should not rely on magically knowing the database keys.
     */
    $neography = Neography::where('machine_name', 'myneography')->firstOrFail();

    // Perform test
    $sanitizer = new BodyTextRenderer;
    $inputHtml = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word in anchor syntax: <a href="/tollerus?id=AAAI" data-tollerus="word" data-id="AAAI" data-lang="myconlang">wumpus</a></p>
        <p>Here is a Tollerus word in span syntax: <span data-tollerus="word" data-id="AAAI" data-lang="myconlang">wumpus</span></p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="{$neography->id}" data-neography="myneography"></span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈwʌmˌpəs</span></p>
        <p>Here is a hyperlink: <a href="https://example.com">click me</a></p>
    </div>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word in anchor syntax: wumpus</p>
        <p>Here is a Tollerus word in span syntax: wumpus</p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="{$neography->id}" data-neography="myneography" class="tollerus_custom_myneography"></span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈwʌmˌpəs</span></p>
        <p>Here is a hyperlink: click me</p>
    </div>
    HTM;
    $outputHtmlRaw = $sanitizer->render($inputHtml, false);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});

it('correctly renders HTML for default display', function () {
    // Set up database context
    (new FileImportSeeder())->run();
    /**
     * After each test, the database changes are rolled back. This doesn't
     * necessarily reset the MySQL auto-increment counters, which means our
     * test should not rely on magically knowing the database keys.
     */
    $neography = Neography::where('machine_name', 'myneography')->firstOrFail();

    // Perform test
    $sanitizer = new BodyTextRenderer;
    $inputHtml = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word in anchor syntax: <a href="/tollerus?id=AAAI" data-tollerus="word" data-id="AAAI" data-lang="myconlang">wumpus</a></p>
        <p>Here is a Tollerus word in span syntax: <span data-tollerus="word" data-id="AAAI" data-lang="myconlang">wumpus</span></p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="{$neography->id}" data-neography="myneography"></span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈwʌmˌpəs</span></p>
        <p>Here is a hyperlink: <a href="https://example.com">click me</a></p>
    </div>
    HTM;
    $expectedHtmlRaw = <<<HTM
    <div>
        <p>Lorem ipsum dolor sit amet.</p>
        <p><span data-tollerus="smallcaps">This is in small caps.</span></p>
        <p>Here is a Tollerus word in anchor syntax: <a href="/tollerus?id=AAAI" data-tollerus="word" data-id="AAAI" data-lang="myconlang">wumpus</a></p>
        <p>Here is a Tollerus word in span syntax: <a data-tollerus="word" data-id="AAAI" href="/tollerus?id=AAAI" data-lang="myconlang">wumpus</a></p>
        <p>Here is some native text: <span data-tollerus="native" data-neography-id="{$neography->id}" data-neography="myneography" class="tollerus_custom_myneography"></span></p>
        <p>Here is some phonemic text: <span data-tollerus="phonemic">ˈwʌmˌpəs</span></p>
        <p>Here is a hyperlink: <a href="https://example.com">click me</a></p>
    </div>
    HTM;
    $outputHtmlRaw = $sanitizer->render($inputHtml);
    $expectedHtml = trim(preg_replace('/>\s+</', '><', $expectedHtmlRaw));
    $outputHtml = trim(preg_replace('/>\s+</', '><', $outputHtmlRaw));
    expect($outputHtml)->toBe($expectedHtml);
});
