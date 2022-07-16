<?php

namespace App\Core;

use \JsonException;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * Get content from request (POST, PUT, PATCH typically), and JSON decodes it into an array.
     * @return array
     * @throws JsonException
     */
    public function getJsonContentAsArray(): array
    {
        return json_decode($this->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
