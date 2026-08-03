<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreFoundationTest extends TestCase
{
    use RefreshDatabase;
    public function test_api_response_contains_request_id(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertOk()->assertHeader('X-Request-ID');
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }
    public function test_existing_request_id_is_preserved(): void
    {
        $response = $this->withHeader('X-Request-ID', 'agentos-test-request')->getJson('/api/health');
        $response->assertOk()->assertHeader('X-Request-ID', 'agentos-test-request');
    }
    public function test_validation_error_uses_standard_format(): void
    {
        $this->postJson('/api/auth/register', [])->assertUnprocessable()->assertJsonPath('success', false)->assertJsonPath('code', 'VALIDATION_ERROR')->assertJsonStructure(['success', 'message', 'code', 'errors',]);
    }
    public function test_unauthenticated_error_uses_standard_format(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized()->assertJsonPath('success', false)->assertJsonPath('code', 'UNAUTHENTICATED');
    }
    public function test_unknown_api_endpoint_uses_json_format(): void
    {
        $this->getJson('/api/endpoint-tidak-ada')->assertNotFound()->assertJsonPath('success', false)->assertJsonPath('code', 'ENDPOINT_NOT_FOUND');
    }
}
