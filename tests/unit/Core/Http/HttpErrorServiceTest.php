<?php

namespace unit\Core\Http;

use App\Core\Http\Exceptions\HttpBadRequestException;
use App\Core\Http\Exceptions\HttpForbiddenException;
use App\Core\Http\Exceptions\HttpMethodNotAllowedException;
use App\Core\Http\Exceptions\HttpNotFoundException;
use App\Core\Http\Exceptions\HttpUnauthorizedException;
use App\Core\Http\HttpErrorService;
use App\Core\Http\Request;
use ErrorException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HttpErrorServiceTest extends TestCase
{
    private HttpErrorService $httpErrorService;
    private LoggerInterface $logger;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpErrorService = new HttpErrorService();
        $this->logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->onlyMethods(
                [
                    'emergency',
                    'alert',
                    'critical',
                    'error',
                    'warning',
                    'notice',
                    'info',
                    'debug',
                    'log',
                ],
            )
            ->getMock();
    }

    /**
     * Test HTTP 400 error.
     *
     * @return void
     */
    public function testBadRequest(): void
    {
        $this->logger->expects($this->never())->method('log');

        $res = $this->httpErrorService->handleError(new Request(), new HttpBadRequestException());
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $res->getStatusCode());
        $this->assertEquals('400 - Bad Request', $res->getContent());

        $res = $this->httpErrorService->handleError(new Request(), new HttpBadRequestException('Test'));
        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $res->getStatusCode());
        $this->assertEquals('Test', $res->getContent());
    }

    /**
     * Test HTTP 403 error.
     *
     * @return void
     */
    public function testForbidden(): void
    {
        $this->logger->expects($this->never())->method('log');

        $res = $this->httpErrorService->handleError(new Request(), new HttpForbiddenException());
        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $res->getStatusCode());
        $this->assertEquals('403 - Forbidden', $res->getContent());

        $res = $this->httpErrorService->handleError(new Request(), new HttpForbiddenException('Test'));
        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $res->getStatusCode());
        $this->assertEquals('Test', $res->getContent());
    }

    /**
     * Test HTTP 404 error.
     *
     * @return void
     */
    public function testNotFound(): void
    {
        $this->logger->expects($this->never())->method('log');

        $res = $this->httpErrorService->handleError(new Request(), new HttpNotFoundException());
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $res->getStatusCode());
        $this->assertEquals('404 - Not Found', $res->getContent());

        $res = $this->httpErrorService->handleError(new Request(), new HttpNotFoundException('Test'));
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $res->getStatusCode());
        $this->assertEquals('Test', $res->getContent());
    }

    /**
     * Test HTTP 405 error.
     *
     * @return void
     */
    public function testMethodNotAllowed(): void
    {
        $this->logger->expects($this->never())->method('log');

        $res = $this->httpErrorService->handleError(new Request(), new HttpMethodNotAllowedException());
        $this->assertEquals(JsonResponse::HTTP_METHOD_NOT_ALLOWED, $res->getStatusCode());
        $this->assertEquals('405 - Method Not Allowed', $res->getContent());

        $res = $this->httpErrorService->handleError(new Request(), new HttpMethodNotAllowedException('Test'));
        $this->assertEquals(JsonResponse::HTTP_METHOD_NOT_ALLOWED, $res->getStatusCode());
        $this->assertEquals('Test', $res->getContent());
    }

    /**
     * Test HTTP 500 error.
     *
     * @return void
     */
    public function testServerError(): void
    {
        $this->logger->expects($this->never())->method('log');

        $res = $this->httpErrorService->handleError(new Request(), new ErrorException());
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $res->getStatusCode());
        $this->assertEquals('500 - Internal Server Error', $res->getContent());

        $res = $this->httpErrorService->handleError(new Request(), new ErrorException('Test'));
        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $res->getStatusCode());
        $this->assertEquals('Test', $res->getContent());
    }

    /**
     * Test HTTP 401 error.
     *
     * @return void
     */
    public function testUnauthorized(): void
    {
        $this->logger->expects($this->never())->method('log');

        $res = $this->httpErrorService->handleError(new Request(), new HttpUnauthorizedException());
        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $res->getStatusCode());
        $this->assertEquals('401 - Unauthorized', $res->getContent());

        $res = $this->httpErrorService->handleError(new Request(), new HttpUnauthorizedException('Test'));
        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $res->getStatusCode());
        $this->assertEquals('Test', $res->getContent());
    }

    public function testLoggingMatchingStatusDefaultMessage(): void
    {
        $httpErrorService = new HttpErrorService($this->logger, [400, 500]);

        $request = new Request();
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK)->setContent('XXX');

        // Make sure the response object is modified.
        $res = clone $response;
        $res->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setContent(Response::HTTP_BAD_REQUEST . ' - ' . Response::$statusTexts[Response::HTTP_BAD_REQUEST]);

        $this
            ->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                Response::HTTP_BAD_REQUEST . ' - ' . Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                ['', $request, $response],
            );
        $httpErrorService->handleError(new Request(), new HttpBadRequestException(), $response);
    }

    public function testLoggingMatchingStatusWithMessage(): void
    {
        $httpErrorService = new HttpErrorService($this->logger, [400, 500]);

        $request = new Request();
        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_OK)->setContent('XXX');

        // Make sure the response object is modified.
        $res = clone $response;
        $res->setStatusCode(Response::HTTP_BAD_REQUEST)
            ->setContent('Test');

        $this
            ->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                Response::HTTP_BAD_REQUEST . ' - ' . Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                ['Test', $request, $res],
            );
        $httpErrorService->handleError(new Request(), new HttpBadRequestException('Test'), $response);
    }

    public function testLoggingNotMatching(): void
    {
        $httpErrorService = new HttpErrorService($this->logger, [400, 500]);

        $this
            ->logger
            ->expects($this->never())
            ->method('error');

        $httpErrorService->handleError(new Request(), new HttpNotFoundException('Test'));
    }
}
