<?php

/**
 * The GlobalIDs use RFC 4648 §5, Base64 URL-safe format
 * https://datatracker.ietf.org/doc/html/rfc4648#section-5
 */
it('correctly encodes a URL-safe base64 global ID', function () {
    config(['tollerus.global_id_digits' => 4]); // Explicitly define what config we are testing
    $model = new \PeterMarkley\Tollerus\Models\GlobalId();
    $model->setRawAttributes(['global_id_raw' => 1]);
    expect($model->global_id)->toBe("AAAB");
    $model->setRawAttributes(['global_id_raw' => 123]);
    expect($model->global_id)->toBe("AAB7");
    $model->setRawAttributes(['global_id_raw' => 8908351]);
    expect($model->global_id)->toBe("h-4_");
});
it('correctly decodes a URL-safe base64 global ID', function () {
    config(['tollerus.global_id_digits' => 4]); // Explicitly define what config we are testing
    $model = new \PeterMarkley\Tollerus\Models\GlobalId();
    $model->global_id = "AAAB";
    expect($model->global_id_raw)->toBe(1);
    $model->global_id = "AAB7";
    expect($model->global_id_raw)->toBe(123);
    $model->global_id = "h-4_";
    expect($model->global_id_raw)->toBe(8908351);
    // Test tolerance of no leading 'A' chars
    $model->global_id = "B";
    expect($model->global_id_raw)->toBe(1);
    $model->global_id = "g";
    expect($model->global_id_raw)->toBe(32);
});
