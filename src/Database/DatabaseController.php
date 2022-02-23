<?php

declare(strict_types=1);

namespace App\Database;

use App\Authentication\AuthenticationInterface;
use App\Authentication\AuthenticationService;
use App\Core\BaseController;
use App\Core\Http\Exceptions\HttpBadRequestException;
use GeekLab\Conf\GLConf;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Core\Request;

class DatabaseController extends BaseController implements AuthenticationInterface
{
    private DatabaseService $databaseService;

    public function __construct(
        GLConf $config,
        Request $request,
        JsonResponse $response,
        AuthenticationService $authenticationService,
        DatabaseService $databaseService
    ) {
        parent::__construct($config, $request, $response, $authenticationService);

        $this->databaseService = $databaseService;
    }

    /**
     * Return a list of databases, filtered by excluded.
     *
     * @return JsonResponse
     */
    public function getDatabases(): JsonResponse
    {
        $data = ['databases' => []];

        /** @var object $jwt */
        $jwt = $this->authenticationService->getToken();
        $excludedTables = $this->databaseService->getExcludedDatabases($jwt->data->host);
        $dbs = $this->databaseService->getDatabases();
        foreach ($dbs as $db) {
            if (!in_array($db, $excludedTables, true)) {
                $data['databases'][] = $db;
            }
        }

        $this->response->setData($data);

        return $this->response;
    }

    /**
     * Return a list of tables in a database.
     *
     * @param array $data
     *
     * @return JsonResponse
     * @throws HttpBadRequestException
     */
    public function getTables(array $data): JsonResponse
    {
        /** @var object $jwt */
        $jwt = $this->authenticationService->getToken();

        return new JsonResponse($this->databaseService->getTables($jwt->data->host, $data['database']));
    }
}
