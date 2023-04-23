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
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HttpErrorServiceTest extends TestCase
{
    private HttpErrorService $httpErrorService;
    private LoggerInterface $logger;
    private Generator $faker;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

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
        $this->httpErrorService = new HttpErrorService($this->logger);

        // Use the factory to create a Faker\Generator instance.
        $this->faker = Factory::create();
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
     * Test logging functionality, using the defaulted message.
     *
     * @return void
     */
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

        $exception = new HttpBadRequestException();
        $this
            ->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                Response::HTTP_BAD_REQUEST . ' - ' . Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                [$exception, $request, $response],
            );
        $httpErrorService->handleError(new Request(), $exception, $response);
    }

    /**
     * Test logging functionality using a passed-in message.
     *
     * @return void
     */
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

        $exception = new HttpBadRequestException('Test');
        $this
            ->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                Response::HTTP_BAD_REQUEST . ' - ' . Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                [$exception, $request, $res],
            );
        $httpErrorService->handleError(new Request(), $exception, $response);
    }

    /**
     * Test logging functionality of an unknown exception object.
     *
     * @return void
     */
    public function testLoggingNotMatching(): void
    {
        $httpErrorService = new HttpErrorService($this->logger, [400, 500]);

        $this->logger->expects($this->never())->method('error');

        $httpErrorService->handleError(new Request(), new HttpNotFoundException('Test'));
    }

    /**
     * Test that logging takes in the body content of request when a PATCH request is made and logger is null.
     *
     * @return void
     */
    public function testLoggingWithPatchMethod(): void
    {
        $this->loggingWithMethodsThatHaveABodyTest(Request::METHOD_PATCH);
    }

    /**
     * Test that logging takes in the body content of request when a POST request is made and logger is null.
     *
     * @return void
     */
    public function testLoggingWithPostMethod(): void
    {
        $this->loggingWithMethodsThatHaveABodyTest(Request::METHOD_POST);
    }

    /**
     * Test that logging takes in the body content of request when a PUT request is made and logger is null.
     *
     * @return void
     */
    public function testLoggingWithPutMethod(): void
    {
        $this->loggingWithMethodsThatHaveABodyTest(Request::METHOD_PUT);
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

    private function loggingWithMethodsThatHaveABodyTest(string $method): void
    {
        $httpErrorService = new HttpErrorService(null, [400, 500]);

        // Create a PATCH Request object with JSON body.
        $content = ['name' => $this->faker->word()];
        $request = new Request(
            server: ['REQUEST_URI' => '/testing' . $method, 'REQUEST_METHOD' => $method],
            content: json_encode($content)
        );

        // Set the temporary file (stream) to store error_log entries in.
        $errorLogTmpFile = tmpFile();
        $errorLogLocationBackup = ini_set('error_log', stream_get_meta_data($errorLogTmpFile)['uri']);

        $exception = new HttpBadRequestException();
        $httpErrorService->handleError($request, $exception, new JsonResponse());

        // Set error_log back to the default.
        ini_set('error_log', $errorLogLocationBackup);

        $result = stream_get_contents($errorLogTmpFile);
        $this->assertMatchesRegularExpression('/"method":"' . $method .'"/', $result);
        $this->assertMatchesRegularExpression('/"body":{"name":"' . $content['name'] . '"}/', $result);
        $this->assertMatchesRegularExpression('/\\/testing' . $method . '"/', $result);
    }
}
