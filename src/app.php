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
$app->map(['<method>'], '<url>', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

    /* your code here */

    $email = null; /* assign email here */

    $user = $auth->getUser($email);

    if (!$user instanceof User) {
        /* your code here */
    }

    $token = $auth->generateToken($user->email());

    /* your code here */

    return $response;
});

//Register new reader
$app->map(['<method>'], '<url>', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

    /* your code here */

    $auth->registerUser(/* arguments */ ['reader']);

    /* your code here */

    return $response;
});

//Add book to library
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $bookId = Uuid::uuid4();
    $library->addBook($bookId /* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List books in library
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $content = json_encode($library->listOfBooks(/* arguments */));

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

//Create reservation for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->createReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);;

//Give away reservation for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveAwayBookInReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);;

//Give back book from reservation
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveBackBookFromReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List reservations for book
$app->map(['<method>'], '<url>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $content = json_encode($library->listReservationsForBook(/* arguments */));

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

return $app;
