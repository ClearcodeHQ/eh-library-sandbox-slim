<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthenticationMiddleware;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthorizationMiddleware;
use Ramsey\Uuid\Uuid;
use Clearcode\EHLibraryAuth\Model\User;

$container = new \Slim\Container;
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$app = new \Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 86400));

$library = new \Clearcode\EHLibrary\Application();
$auth = new \Clearcode\EHLibraryAuth\Application();
$authenticationMiddleware = new AuthenticationMiddleware($auth);

//Login user (login by email only - no password)
$app->map(['POST'], '/login', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

    /* your code here */

    $body = $request->getParsedBody();

    $email = $body['email']; /* assign email here */

    $user = $auth->getUser($email);

    if (!$user instanceof User) {
        /* your code here */
        return $response->withStatus(401);
    }

    $token = $auth->generateToken($user->email());

    /* your code here */
    $body = $response->getBody();
    $body->write($token);

    $response = $response->withBody($body);
    $response = $response->withHeader('Content-Type', 'application/json');

    return $response;
});

//Register new reader
$app->map(['POST'], '/register', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

    /* your code here */

    $body = $request->getParsedBody();

    $auth->registerUser($body['email'], ['reader']);

    /* your code here */

    return $response;
});

//Add book to library
$app->map(['POST'], '/books', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $body = $request->getParsedBody();
    $title = $body['title'];
    $authors = $body['authors'];
    $isbn = $body['isbn'];

    $bookId = Uuid::uuid4();
    $library->addBook($bookId, $title, $authors, $isbn);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List books in library
$app->map(['GET'], '/books', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $content = json_encode($library->listOfBooks(/* arguments */));

    $body = $response->getBody();
    $body->write($content);

    $response = $response->withBody($body);
    $response = $response->withHeader('Content-Type', 'application/json');

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

//Create reservation for book
$app->map(['<method>'], '<url3>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->createReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);;

//Give away reservation for book
$app->map(['<method>'], '<url4>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveAwayBookInReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);;

//Give back book from reservation
$app->map(['<method>'], '<url5>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveBackBookFromReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List reservations for book
$app->map(['<method>'], '<url6>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $content = json_encode($library->listReservationsForBook(/* arguments */));

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

return $app;
