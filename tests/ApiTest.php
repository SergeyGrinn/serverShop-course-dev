<?php

use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        $this->baseUrl = rtrim(getenv('API_TEST_BASE_URL') ?: 'http://servershop.test', '/');
    }

    public function testHealthEndpointReturnsSuccessForGet(): void
    {
        $response = $this->request('GET');
        $data = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response['status']);
        self::assertTrue($data['success']);
        self::assertSame('API is working correctly', $data['message']);
    }

    public function testHealthEndpointRejectsPost(): void
    {
        $response = $this->request('POST');
        $data = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(405, $response['status']);
        self::assertFalse($data['success']);
        self::assertSame('Request must be GET', $data['message']);
    }

    /** @return array{status: int, body: string} */
    private function request(string $method): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'ignore_errors' => true,
                'timeout' => 5,
            ],
        ]);

        $body = @file_get_contents($this->baseUrl . '/api/test.php', false, $context);
        $statusLine = $http_response_header[0] ?? '';
        preg_match('/\s(\d{3})(?:\s|$)/', $statusLine, $matches);

        return [
            'status' => (int) ($matches[1] ?? 0),
            'body' => $body === false ? '' : $body,
        ];
    }
}