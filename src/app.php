<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthenticationMiddleware;

$app = new \Slim\App;
$library = new \Clearcode\EHLibrary\Application();
$authenticationMiddleware = new AuthenticationMiddleware($library);

//Add book to library
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->addBook(/* arguments */);

    /* your code here */

    return $response;
});

//List books in library
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $responseBody = json_encode($library->listOfBooks(/* arguments */));

    /* your code here */

    return $response;
});


//Create reservation for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->createReservation(/* arguments */);

    /* your code here */

    return $response;
});

//Give away reservation for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveAwayBookInReservation(/* arguments */);

    /* your code here */

    return $response;
});

//Give back book from reservation
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveBackBookFromReservation(/* arguments */);

    /* your code here */

    return $response;
});

//List reservations for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $responseBody = json_encode($library->listReservationsForBook(/* arguments */));

    /* your code here */

    return $response;
});

return $app->add($authenticationMiddleware);
