<?php

namespace tests\Clearcode\EHLibrarySandbox\Slim;

use Clearcode\EHLibrary\Infrastructure\Persistence\LocalBookRepository;
use Clearcode\EHLibrary\Infrastructure\Persistence\LocalReservationRepository;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

abstract class WebTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var Response */
    protected $response;
    /** @var App */
    private $app;

    /**
     * @param string $method
     * @param string $url
     * @param array $routeParameters
     * @param array $requestParameters
     */
    protected function request($method, $url, array $routeParameters, array $requestParameters)
    {
        $request = $this->prepareRequest($method, $url, $routeParameters, $requestParameters);
        $response = new Response();

        $app = $this->app;
        $this->response = $app($request, $response);;
    }

    protected function assertThatResponseHasStatus($expectedStatus)
    {
        $this->assertEquals($expectedStatus, $this->response->getStatusCode());
    }

    protected function assertThatResponseHasContentType($expectedContentType)
    {
        $this->assertContains($expectedContentType, $this->response->getHeader('Content-Type'));
    }

    protected function assertThatResponseBodyContains($expectedString)
    {
        $this->assertContains($expectedString, (string) $this->response->getBody());
    }

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->clearDatabase();

        $this->app =  $this->getApp();
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->app = null;
        $this->response = null;
    }

    private function getApp()
    {
        return require __DIR__.'/../src/app.php';
    }

    private function prepareRequest($method, $url, array $routeParameters, array $requestParameters)
    {
        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => $url,
            'REQUEST_METHOD' => $method,
        ]);

        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);
        $cookies = [];

        $serverParams = $env->all();

        $body = new RequestBody();
        $body->write(json_encode($requestParameters));

        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        return $request->withHeader('Content-Type', 'application/json');
    }

    private function clearDatabase()
    {
        (new LocalBookRepository())->clear();
        (new LocalReservationRepository())->clear();
    }
}
