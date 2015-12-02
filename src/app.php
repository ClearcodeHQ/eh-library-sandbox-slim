<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Clearcode\EHLibrary\Model\BookInReservationAlreadyGivenAway;

$app = new \Slim\App;
$library = new \Clearcode\EHLibrary\Application();

$bookDataValidator = function (array $bookData = null) {
    if ($bookData === null || !isset($bookData['title']) || !isset($bookData['authors']) || !isset($bookData['isbn'])) {
        return false;
    }

    return true;
};

$reservationDataValidator = function (array $reservationData = null) {
    if ($reservationData === null || !isset($reservationData['email'])) {
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
$app->post('/books/{bookId}/reservations', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $bookId = Uuid::fromString($args['bookId']);

    $requestBody = $request->getParsedBody();

    if ($reservationDataValidator($requestBody) == false) {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    $library->createReservation($bookId, $requestBody['email']);

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

//Give away reservation for book
$app->patch('/books/{bookId}/reservations/{reservationId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $reservationId = Uuid::fromString($args['reservationId']);

    try {
        $library->giveAwayBookInReservation($reservationId);
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
$app->delete('/books/{bookId}/reservations/{reservationId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $reservationId = Uuid::fromString($args['reservationId']);
    $library->giveBackBookFromReservation($reservationId);

    return $response
        ->withStatus(204);
});

//List reservations for book
$app->get('/books/{bookId}/reservations', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $app, $reservationDataValidator) {

    $bookId = Uuid::fromString($args['bookId']);

    $responseBody = $response->getBody();
    $responseBody->write(json_encode($library->listReservationsForBook($bookId)));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200)
        ->withBody($responseBody);
});

return $app;