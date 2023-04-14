<?php

declare(strict_types=1);

namespace App\Database;

use App\Authentication\AuthenticationInterface;
use App\Authentication\AuthenticationService;
use App\Core\BaseController;
use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\Request;
use GeekLab\Conf\GLConf;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @OA\Get(
     *     path="/databases",
     *     summary="Require authentication",
     *     @OA\Response(
     *         response="200",
     *         description="Get a list of List of databases from a server, filter by excluded",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="mysql", type="string", example="DB1", description="The mysql name"),
     *             ),
     *         ),
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getDatabases(): JsonResponse
    {
        $data = [];

        /** @var object $jwt */
        $jwt = $this->authenticationService->getToken();
        $excludedTables = $this->databaseService->getExcludedDatabases($jwt->data->host);
        $dbs = $this->databaseService->getDatabases();
        foreach ($dbs as $db) {
            if (!in_array($db, $excludedTables, true)) {
                $data[] = $db;
            }
        }

        $this->response->setData($data);

        return $this->response;
    }

    /**
     * Return a list of tables in a mysql.
     *
     * @OA\Get(
     *     path="/databases/{mysql}/tables",
     *     summary="Require authentication",
     *     @OA\Response(
     *         response="200",
     *         description="List of tables",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="table", type="string", example="myTable", description="Table name"),
     *                 @OA\Property(property="size", type="integer", example="1024", description="Table size in bytes"),
     *             ),
     *         ),
     *     )
     * )
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

        return new JsonResponse($this->databaseService->getTables($jwt->data->host, $data['mysql']));
    }
}
