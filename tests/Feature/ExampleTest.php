<?php

it('returns a successful response', function () {
    $response = $this->get(route('tollerus.public.index', absolute: false));

    $response->assertStatus(200);
});
