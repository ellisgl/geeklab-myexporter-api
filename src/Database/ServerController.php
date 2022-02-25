<?php

declare(strict_types=1);

namespace App\Database;

use App\Core\BaseController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

class ServerController extends BaseController
{
    /**
     * Return array of indexed server names. No authentication needed.
     *
     * @OA\Get(path="/servers", @OA\Response(response="200", description="List of servers"))
     * @return JsonResponse
     */
    public function getServers(): JsonResponse
    {
        $servers = [];
        foreach($this->config->get('servers') as $key => $value) {
            $servers[] = ['id' => $key, 'name' => $value['name']];
        }

        return new JsonResponse($servers);
    }
}
