<?php

test('legacy showthread.php URL with id only redirects to named archive thread route', function () {
    $response = $this->get('/forum/showthread.php?48553');

    $response->assertStatus(301);
    $response->assertRedirect(route('archive.thread', '48553'));
});

test('legacy showthread.php URL with id followed by trailing equals redirects to named archive thread route', function () {
    $response = $this->get('/forum/showthread.php?48553=');

    $response->assertStatus(301);
    $response->assertRedirect(route('archive.thread', '48553'));
});

test('legacy showthread.php URL with empty query string redirects to archive index', function () {
    $response = $this->get('/forum/showthread.php');

    $response->assertStatus(301);
    $response->assertRedirect('/archive');
});
