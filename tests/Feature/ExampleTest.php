<?php

it('loads public index without error', function () {
    $response = $this->get(route('tollerus.public.index', absolute: false));
    $response->assertStatus(200);
});
it('loads admin index without error', function () {
    $response = $this->get(route('tollerus.admin.index', absolute: false));
    $response->assertStatus(200);
});
