<?php

namespace tests\Clearcode\EHLibrarySandbox\Slim;

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
     * @param string $url
     */
    protected function request($url)
    {
        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => $url,
            'REQUEST_METHOD' => 'GET',
        ]);

        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();

        $body = new RequestBody();
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body);

        $app = $this->app;
        $this->response = $app($request, new Response());;
    }

    /** {@inheritdoc} */
    protected function setUp()
    {
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
}
