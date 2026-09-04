<?php

test('404 error page renders custom view content', function (): void {
    $response = $this->get('/non-existent-page');

    $response->assertStatus(404);
    $response->assertSee('404', true);
    $response->assertSee(__('Not Found'), true);
    $response->assertSee(__('Go Home'), true);
});
