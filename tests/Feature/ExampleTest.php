<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    // Home page redirects to login for unauthenticated users
    $response->assertStatus(302);
});
