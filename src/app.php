<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthenticationMiddleware;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthorizationMiddleware;
use Ramsey\Uuid\Uuid;
use Clearcode\EHLibraryAuth\Model\User;

$app = new \Slim\App;
$library = new \Clearcode\EHLibrary\Application();
$auth = new \Clearcode\EHLibraryAuth\Application();
$authenticationMiddleware = new AuthenticationMiddleware($auth);

//Login user (login by email only - no password)
$app->map(['<method>'], '<url1>', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

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
$app->map(['<method>'], '<url2', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth /* dependencies */) {

    /* your code here */

    $auth->registerUser(/* arguments */ ['reader']);

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

    $bookId = isset($body['bookId']) ? Uuid::fromString($body['bookId']) : Uuid::uuid4();
    $library->addBook($bookId, $title, $authors, $isbn);

    /* your code here */

    return $response;
});
    //->add(new AuthorizationMiddleware(['librarian']))
    //->add($authenticationMiddleware);

//List books in library
$app->map(['GET'], '/books', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $content = json_encode($library->listOfBooks(/* arguments */));

    /* your code here */

    $body = $response->getBody();
    $body->write($content);

    $response = $response->withHeader('Content-Type', 'application/json');

    $eTag = md5($content);
    $response = $response->withHeader('ETag', $eTag);

    //$response = $response->withHeader('Last-Modified', (new DateTime())->format('D, d M Y H:i:s \G\M\T'));
    //$response = $response->withHeader('Cache-Control', 'max-age=' . (time() + 60));
    //$response = $response->withHeader('Expires', (new DateTime('+1 hour'))->format('D, d M Y H:i:s \G\M\T'));

    $ifNoneMatch = $request->getHeader('If-None-Match');

    if ($ifNoneMatch && current($ifNoneMatch) == $eTag) {
        $response = $response->withStatus(304);
    } else {
        $response = $response->withBody($body);
    }

    return $response;
});
    //->add(new AuthorizationMiddleware(['reader', 'librarian']))
    //->add($authenticationMiddleware);

//Create reservation for book
$app->map(['<method>'], '<url5>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->createReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);;

//Give away reservation for book
$app->map(['<method>'], '<url6>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveAwayBookInReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);;

//Give back book from reservation
$app->map(['<method>'], '<url7>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $library->giveBackBookFromReservation(/* arguments */);

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List reservations for book
$app->map(['<method>'], '<url8>', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library /* dependencies */) {

    /* your code here */

    $content = json_encode($library->listReservationsForBook(/* arguments */));

    /* your code here */

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

return $app;
