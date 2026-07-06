<?php

it('correctly encodes a base64 ID', function () {
    config(['tollerus.global_id_digits' => 4]); // Explicitly define what config we are testing
    $model = new \PeterMarkley\Tollerus\Models\GlobalId();
    $model->setRawAttributes(['global_id_raw' => 1]);
    expect($model->global_id)->toBe("AAAB");
});
