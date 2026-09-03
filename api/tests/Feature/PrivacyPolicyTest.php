<?php

test('privacy page renders successfully with default or dropjdid target after slash', function () {
    $response = $this->get('/privacy/dropjdid');

    $response->assertOk();
    $response->assertSee('<title>Dropjdid - Privacy Policy</title>', false);
    $response->assertSee('Privacy Policy for <span>Dropjdid</span>', false);

    // Also verify base route /privacy defaults to dropjdid
    $defaultResponse = $this->get('/privacy');
    $defaultResponse->assertOk();
    $defaultResponse->assertSee('<title>Dropjdid - Privacy Policy</title>', false);
});

test('privacy page renders successfully with octaprize target after slash', function () {
    $response = $this->get('/privacy/octaprize');

    $response->assertOk();
    $response->assertSee('<title>Octaprize - Privacy Policy</title>', false);
    $response->assertSee('Privacy Policy for <span>Octaprize</span>', false);
    $response->assertSee('privacy@octaprize.com');
});

test('account deletion page renders with dynamic target after slash', function () {
    $responseDrop = $this->get('/account-deletion/dropjdid');
    $responseDrop->assertOk();
    $responseDrop->assertSee('Delete Your <span>Dropjdid</span> Account', false);

    $responseOcta = $this->get('/account-deletion/octaprize');
    $responseOcta->assertOk();
    $responseOcta->assertSee('Delete Your <span>Octaprize</span> Account', false);
    $responseOcta->assertSee('support@octaprize.com');

    // Also verify default route /account-deletion
    $defaultResponse = $this->get('/account-deletion');
    $defaultResponse->assertOk();
    $defaultResponse->assertSee('Delete Your <span>Dropjdid</span> Account', false);
});
