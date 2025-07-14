<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Kernel;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthTest extends ApiTestCase
{

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testLoginAndAccessProtectedEndpoint(): void
    {
        $client = static::createClient();

        // Logowanie i pobranie tokena
        $response = $client->request('POST', '/api/login_check', [
            'json' => [
                'username' => 'admin@example.com',
                'password' => 'adminpass'
            ]
        ]);
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);

        // Próba wejścia na chroniony endpoint z tokenem
        $client->request('GET', '/api/admin', [
            'headers' => [
                'Authorization' => 'Bearer ' . $data['token'],
            ],
        ]);

        $this->assertResponseIsSuccessful();

    }
}
