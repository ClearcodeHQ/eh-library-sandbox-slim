<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthenticationMiddleware;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthorizationMiddleware;

$app = new \Slim\App;
$library = new \Clearcode\EHLibrary\Application();
$auth = new \Clearcode\EHLibraryAuth\Application();
$userRepository = new \Clearcode\EHLibraryAuth\Infrastructure\Persistence\LocalUserRepository();
$authenticationMiddleware = new AuthenticationMiddleware($userRepository);

$app->map(['GET'], '/books', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) {

    $responseBody = $response->getBody();
    $responseBody->write('Yo!');

    return $response->withBody($responseBody);
})->add(new AuthorizationMiddleware(['reader', 'librarian']));

//Login user
$app->map(['<method>'], '<url>', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

    /**
     * generate JWT and return it in response
     */

    return $response;
});

////Register new reader
//$app->map(['<method>'], '<url>', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {
//
//    /* your code here */
//
//    $roles = ['reader'];
//    $auth->registerUser(/* arguments */ $roles);
//
//    /* your code here */
//
//    return $response;
//});
//
////Add book to library
//$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {
//
//    /* your code here */
//
//    $library->addBook(/* arguments */);
//
//    /* your code here */
//
//    return $response;
//});
//
////List books in library
//$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {
//
//    /* your code here */
//
//    $content = json_encode($library->listOfBooks(/* arguments */));
//
//    /* your code here */
//
//    return $response;
//});
//
////Create reservation for book
//$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {
//
//    /* your code here */
//
//    $library->createReservation(/* arguments */);
//
//    /* your code here */
//
//    return $response;
//});
//
////Give away reservation for book
//$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {
//
//    /* your code here */
//
//    $library->giveAwayBookInReservation(/* arguments */);
//
//    /* your code here */
//
//    return $response;
//});
//
////Give back book from reservation
//$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {
//
//    /* your code here */
//
//    $library->giveBackBookFromReservation(/* arguments */);
//
//    /* your code here */
//
//    return $response;
//});
//
////List reservations for book
//$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {
//
//    /* your code here */
//
//    $content = json_encode($library->listReservationsForBook(/* arguments */));
//
//    /* your code here */
//
//    return $response;
//});

$app->add($authenticationMiddleware);

return $app;
