<?php

test('clients can fetch hardcoded learning videos', function () {
    $response = $this->getJson('/api/learning/videos');

    $response
        ->assertOk()
        ->assertJsonCount(4, 'data')
        ->assertJsonPath('data.0.id', 1)
        ->assertJsonPath('data.0.title', 'I\'m SGM, No way I\'m gonna lose')
        ->assertJsonPath('data.0.published_at', '01 mai 2025')
        ->assertJsonPath('data.0.video_url', 'https://www.youtube.com/watch?v=sgm-1');
});
