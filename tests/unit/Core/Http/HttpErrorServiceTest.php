<?php

namespace unit\Core\Http;

use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\HttpErrorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class HttpErrorServiceTest extends TestCase
{
    private HttpErrorService $httpErrorService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpErrorService = new HttpErrorService();
    }

    /**
     * This will test most of the error response generators full, except the 500 generator.
     *
     * @return void
     */
    public function testHandleError(): void
    {
        $res = $this->httpErrorService->handleError(new HttpBadRequestException());
        self::assertEquals(JsonResponse::HTTP_BAD_REQUEST, $res->getStatusCode());
        self::assertEquals('400 - Bad request', $res->getContent());

        $res = $this->httpErrorService->handleError(new HttpBadRequestException('Test'));
        self::assertEquals(JsonResponse::HTTP_BAD_REQUEST, $res->getStatusCode());
        self::assertEquals('Test', $res->getContent());
    }

    public function testError500WithMessage(): void
    {
        $res = $this->httpErrorService->error500('Test');
        self::assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $res->getStatusCode());
        self::assertEquals('Test', $res->getContent());
    }
}
