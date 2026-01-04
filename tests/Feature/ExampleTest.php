<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_health_route_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)->assertSeeText('ok');
    }
}
