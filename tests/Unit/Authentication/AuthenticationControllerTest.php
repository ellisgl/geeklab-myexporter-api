<?php

namespace Test\Unit\Authentication;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;
use Test\Unit\ControllerTestCase;

class AuthenticationControllerTest extends ControllerTestCase
{
    /**
     * @return void
     * @throws GuzzleException
     */
    public function testLogin(): void
    {
        $response = $this->client->request(
            'POST',
            'login',
            ['body' => json_encode(['server_id' => 0, 'username' => 'root', 'password' => 'root'])],
        );
        $contents = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('success', $contents['message']);
    }
}
