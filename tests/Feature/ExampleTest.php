<?php

it('returns a successful response', function () {
    dump(route('tollerus.public.index', absolute: false));
    $response = $this->get(route('tollerus.public.index', absolute: false));

    $response->assertStatus(200);
});
