<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Clearcode\EHLibrary\Model\BookInReservationAlreadyGivenAway;
use Clearcode\EHLibrary\Model\CannotGiveBackReservationWhichWasNotGivenAway;

$app = new \Slim\App;
$library = new \Clearcode\EHLibrary\Application();

$bookDataValidator = function (array $bookData = null) {
    if ($bookData === null || !isset($bookData['title']) || !isset($bookData['authors']) || !isset($bookData['isbn'])) {
        return false;
    }

    return true;
};

$givenAwayValidator = function (array $givenAwayData = null) {
    if ($givenAwayData === null || !isset($givenAwayData['givenAwayAt'])) {
        return false;
    }

    return true;
};

$reservationDataValidator = function (array $reservationData = null) {
    if ($reservationData === null || !isset($reservationData['email']) || !isset($reservationData['bookId'])) {
        return false;
    }

    return true;
};

//Add book to library
$app->put('/books/{bookId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $bookDataValidator) {

    $bookId = Uuid::fromString($args['bookId']);
    $requestBody = $request->getParsedBody();

    if ($bookDataValidator($requestBody) == false) {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $responseBody = $response->getBody();
    $responseBody->write(json_encode(['id' => (string) $bookId]));

    $library->addBook($bookId, $requestBody['title'], $requestBody['authors'], $requestBody['isbn']);

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201)
        ->withBody($responseBody);
});

//List books in library
$app->get('/books', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app) {

    $page = 1;
    $booksPerPage = null;

    $query = $request->getQueryParams();

    if (isset($query['page'])) {
        $page = $query['page'];
    }

    if (isset($query['booksPerPage'])) {
        $booksPerPage = $query['booksPerPage'];
    }

    $responseBody = $response->getBody();
    $responseBody->write(json_encode($library->listOfBooks($page, $booksPerPage)));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200)
        ->withBody($responseBody);
});

//Create reservation for book
$app->post('/reservations', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $reservationId = Uuid::uuid4();
    $requestBody = $request->getParsedBody();

    if ($reservationDataValidator($requestBody) == false) {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $bookId = Uuid::fromString($requestBody['bookId']);

    $library->createReservation($reservationId, $bookId, $requestBody['email']);

    $responseBody = $response->getBody();
    $responseBody->write(json_encode(['id' => (string) $reservationId]));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

//Give away reservation for book
$app->patch('/reservations/{reservationId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $givenAwayValidator) {

    $reservationId = Uuid::fromString($args['reservationId']);
    $requestBody = $request->getParsedBody();

    if ($givenAwayValidator($requestBody) == false) {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    try {
        $library->giveAwayBookInReservation($reservationId, new \DateTime($requestBody['givenAwayAt']));
    } catch (BookInReservationAlreadyGivenAway $e) {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

//Give back book from reservation
$app->delete('/reservations/{reservationId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $reservationId = Uuid::fromString($args['reservationId']);

    try {
        $library->giveBackBookFromReservation($reservationId);
    } catch (CannotGiveBackReservationWhichWasNotGivenAway $e) {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    return $response
        ->withStatus(204);
});

//List reservations for book
$app->get('/reservations', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $query = $request->getQueryParams();

    if (!isset($query['bookId'])) {
        $responseBody = $response->getBody();
        $responseBody->write(json_encode([]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->withBody($responseBody);
    }

    $bookId = Uuid::fromString($query['bookId']);
    $responseBody = $response->getBody();
    $responseBody->write(json_encode($library->listReservationsForBook($bookId)));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200)
        ->withBody($responseBody);
});

return $app;