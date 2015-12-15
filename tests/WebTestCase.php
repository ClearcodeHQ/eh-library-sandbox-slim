<?php

namespace tests\Clearcode\EHLibrarySandbox\Slim;

use Clearcode\EHLibrary\Infrastructure\Persistence\LocalBookRepository;
use Clearcode\EHLibrary\Infrastructure\Persistence\LocalReservationRepository;
use Clearcode\EHLibrary\Model\Book;
use Clearcode\EHLibrary\Model\Reservation;
use Ramsey\Uuid\Uuid;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

abstract class WebTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    protected $jsonResponseData;
    /** @var Response */
    private $response;
    /** @var LocalBookRepository */
    private $books;
    /** @var LocalReservationRepository */
    private $reservations;
    /** @var App */
    private $app;

    protected function addBook($bookId, $title, $authors, $isbn)
    {
        $this->books->save(new Book(Uuid::fromString($bookId), $title, $authors, $isbn));
    }

    protected function addReservation($reservationId, $bookId, \DateTime $givenAwayAt = null)
    {
        $reservation = new Reservation(Uuid::fromString($reservationId), Uuid::fromString($bookId), 'john@doe.com');

        if (null !== $givenAwayAt) {
            $reservation->giveAway($givenAwayAt);
        }

        $this->reservations->save($reservation);
    }

    protected function request($method, $url, array $requestParameters = [], array $headers = [])
    {
        $request = $this->prepareRequest($method, $url, $requestParameters, $headers);
        $response = new Response();

        $app = $this->app;
        $this->response = $app($request, $response);
        $this->jsonResponseData = json_decode((string) $this->response->getBody(), true);
    }

    protected function assertThatResponseHasStatus($expectedStatus)
    {
        $this->assertEquals($expectedStatus, $this->response->getStatusCode());
    }

    protected function assertThatResponseHasContentType($expectedContentType)
    {
        $this->assertContains($expectedContentType, $this->response->getHeader('Content-Type'));
    }

    protected function assertThatResponseHasETagsHeader($response)
    {
        $this->assertContains(md5(json_encode($response)), $this->response->getHeader('ETag'));
    }

    protected function assertThatResponsesHaveDifferentETagHeaders($oldResponse, $newResponse)
    {
        $oldETag = md5(json_encode($oldResponse));
        $newETag = md5(json_encode($newResponse));
        $this->assertNotEquals($oldETag, $newETag);
    }

    protected function getETag()
    {
        return $this->response->getHeader('ETag');
    }

    protected function assertThatResponseHasNotContentType()
    {
        $this->assertEmpty($this->response->getHeader('Content-Type'));
    }

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->books = new LocalBookRepository();
        $this->reservations = new LocalReservationRepository();

        $this->clearDatabase();

        $this->app =  $this->getApp();
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->books = null;
        $this->reservations = null;
        $this->app = null;
        $this->response = null;
        $this->jsonResponseData = null;
    }

    private function getApp()
    {
        return require __DIR__.'/../src/app.php';
    }

    private function prepareRequest($method, $url, array $requestParameters, array $requestHeaders)
    {
        $env = Environment::mock([
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => $url,
            'REQUEST_METHOD' => $method,
        ]);

        $parts = explode('?', $url);

        if (isset($parts[1])) {
            $env['QUERY_STRING'] = $parts[1];
        }

        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);

        foreach ($requestHeaders as $headerName => $headerValue) {
            $headers->add($headerName, $headerValue);
        }

        $cookies = [];

        $serverParams = $env->all();

        $body = new RequestBody();
        $body->write(json_encode($requestParameters));

        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

        return $request->withHeader('Content-Type', 'application/json');
    }

    private function clearDatabase()
    {
        $this->books->clear();
        $this->reservations->clear();
    }
}
