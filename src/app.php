<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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

//authenticate the user with JWT token
$authenticationMiddleware = function (ServerRequestInterface $request, ResponseInterface $response, $next) use ($library /*dependencies*/) {

    /* your code here */

    $user = null; /* assign user here */

    $request = $request->withAttribute('user', $user);
    $response = $next($request, $response);

    return $response;
};

//Add book to library
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->addBook(/* arguments */);

    /* your code here */

    return $response;
})->add($authenticationMiddleware);

//List books in library
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $responseBody = json_encode($library->listOfBooks(/* arguments */));

    /* your code here */

    return $response;
})->add($authenticationMiddleware);


//Create reservation for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->createReservation(/* arguments */);

    /* your code here */

    return $response;
})->add($authenticationMiddleware);

//Give away reservation for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveAwayBookInReservation(/* arguments */);

    /* your code here */

    return $response;
})->add($authenticationMiddleware);

//Give back book from reservation
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveBackBookFromReservation(/* arguments */);

    /* your code here */

    return $response;
})->add($authenticationMiddleware);

//List reservations for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $responseBody = json_encode($library->listReservationsForBook(/* arguments */));

    /* your code here */

    return $response;
})->add($authenticationMiddleware);


return $app;