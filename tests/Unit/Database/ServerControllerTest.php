<?php

namespace Test\Unit\Database;

use GuzzleHttp\Exception\GuzzleException;
use Test\Unit\ControllerTestCase;

use function PHPUnit\Framework\assertEquals;

class ServerControllerTest extends ControllerTestCase
{
    /**
     * @return void
     * @throws GuzzleException
     */
    public function testGetServers(): void
    {
        $response = $this->client->request(
            'GET',
            'servers'
        );

        $contents = json_decode($response->getBody()->getContents(), true);
        foreach ($this->config->get('servers') as $key => $server) {
            assertEquals($server['name'], $contents[$key]['name']);
        }
    }
}
