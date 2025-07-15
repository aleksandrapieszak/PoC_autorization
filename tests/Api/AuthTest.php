<?php

namespace App\Tests\Api;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

class AuthTest extends ApiTestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testLoginAndAccessProtectedEndpoint(): void
    {

        // Logowanie i pobranie tokena
        $response = $this->client->request('POST', '/api/login_check', [
            'json' => [
                'username' => 'admin@example.com',
                'password' => 'adminpass'
            ]
        ]);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        // Próba wejścia na chroniony endpoint z tokenem
        $this->client->request('GET', '/api/admin', [
            'headers' => [
                'Authorization' => 'Bearer ' . $data['token'],
            ],
        ]);

        $this->assertResponseIsSuccessful();

    }

    public function testUserCannotAccessAdminEndpoint(): void
    {

        // Logowanie i pobranie tokena dla zwykłego użytkownika
        $response = $this->client->request('POST', '/api/login_check', [
            'json' => [
                'username' => 'user@example.com', // login zwykłego użytkownika
                'password' => 'adminpass'
            ]
        ]);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        // Próba wejścia na chroniony endpoint admina z tokenem usera
        $this->client->request('GET', '/api/admin', [
            'headers' => [
                'Authorization' => 'Bearer ' . $data['token'],
            ],
        ]);

        // dostęp powinien być zabroniony (403)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testRolesEndpointForAuthenticatedUser(): void
    {
        // Logowanie użytkownika
        $response = $this->client->request('POST', '/api/login_check', [
            'json' => [
                'username' => 'user@example.com',
                'password' => 'adminpass' // lub inny zgodny z bazą testową
            ]
        ]);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        // Dostęp do /api/roles z tokenem użytkownika
        $response = $this->client->request('GET', '/api/roles', [
            'headers' => [
                'Authorization' => 'Bearer ' . $data['token'],
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $json = $response->toArray();
        # sprawdzamy, czy odpowiedź zawiera oczekiwane dane
        $this->assertStringContainsString('Hello, user@example.com', $json['message']);
        $this->assertIsArray($json['roles']);
        $this->assertContains('ROLE_USER', $json['roles']);
    }

    public function testRolesEndpointWithoutAuthentication(): void
    {
        $this->client->request('GET', '/api/roles');
        $this->assertResponseStatusCodeSame(401);
    }

}
