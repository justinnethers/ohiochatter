<?php

it('returns a successful response', function () {
    $response = $this->get('/threads');

    $response->assertStatus(200);
});
