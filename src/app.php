<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthenticationMiddleware;
use Clearcode\EHLibrarySandbox\Slim\Middleware\AuthorizationMiddleware;
use Ramsey\Uuid\Uuid;
use Clearcode\EHLibraryAuth\Model\User;
use Clearcode\EHLibrary\Infrastructure\Persistence\LocalReservationRepository;

$container = new \Slim\Container;
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$app = new \Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 86400));

$library = new \Clearcode\EHLibrary\Application();
$auth = new \Clearcode\EHLibraryAuth\Application();
$authenticationMiddleware = new AuthenticationMiddleware($auth);

$reservationRepository = new LocalReservationRepository();

//Login user (login by email only - no password)
$app->map(['POST'], '/login', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth) {

    $body = $request->getParsedBody();

    $email = $body['email'];

    $user = $auth->getUser($email);

    if (!$user instanceof User) {
        return $response->withStatus(401);
    }

    $token = $auth->generateToken($user->email());

    $body = $response->getBody();
    $body->write($token);

    $response = $response->withBody($body);
    $response = $response->withHeader('Content-Type', 'application/json');

    return $response;
});

//Register new reader
$app->map(['POST'], '/register', function(ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($auth) {

    $body = $request->getParsedBody();

    $roles = ['reader'];

    if (isset($body['is_librarian']) && $body['is_librarian']) {
        $roles[] = 'librarian';
    }

    $auth->registerUser($body['email'], $roles);

    return $response;
});

//Add book to library
$app->map(['PUT'], '/books/{bookId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library) {

    $body = $request->getParsedBody();

    $response = $response->withHeader('Content-Type', 'application/json');

    if (!isset($body['title']) || !isset($body['authors']) || !isset($body['isbn'])) {
        return $response->withStatus(400);
    }

    $title = $body['title'];
    $authors = $body['authors'];
    $isbn = $body['isbn'];

    $bookId = Uuid::fromString($args['bookId']);
    $library->addBook($bookId, $title, $authors, $isbn);

    $body = $response->getBody();
    $body->write(json_encode(['id' => $bookId]));

    $response = $response->withBody($body);
    $response = $response->withStatus(201);

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List books in library
$app->map(['GET'], '/books', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library) {

    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $booksPerPage = isset($queryParams['booksPerPage']) ? $queryParams['booksPerPage'] : null;

    $content = json_encode($library->listOfBooks($page, $booksPerPage));

    $body = $response->getBody();
    $body->write($content);

    $response = $response->withBody($body);
    $response = $response->withHeader('Content-Type', 'application/json');

    $response = $this->cache->withEtag($response, md5($content));

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

//Create reservation for book
$app->map(['POST'], '/reservations', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library) {

    $body = $request->getParsedBody();

    $response = $response->withHeader('Content-Type', 'application/json');

    if (!isset($body['bookId'])) {
        return $response->withStatus(400);
    }

    $bookId = Uuid::fromString($body['bookId']);

    $user = $request->getAttribute('user');

    $library->createReservation(Uuid::uuid4(), Uuid::fromString($bookId), $user->email());

    $body = $response->getBody();
    $body->write(json_encode(['id' => $bookId]));

    $response = $response->withBody($body);
    $response = $response->withStatus(201);

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

//Give away reservation for book
$app->map(['PATCH'], '/reservations/{reservationId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $reservationRepository) {

    $reservationId = Uuid::fromString($args['reservationId']);

    $reservation = $reservationRepository->get($reservationId);

    $response = $response->withHeader('Content-Type', 'application/json');

    if ($reservation->isGivenAway()) {
        return $response->withStatus(400);
    }

    $body = $request->getParsedBody();
    $givenAwayAt = new \DateTime($body['givenAwayAt']);

    $library->giveAwayBookInReservation($reservationId, $givenAwayAt);

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);;

//Give back book from reservation
$app->map(['DELETE'], '/reservations/{reservationId}', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library, $reservationRepository) {

    $reservationId = Uuid::fromString($args['reservationId']);

    $reservation = $reservationRepository->get($reservationId);

    if (!$reservation->isGivenAway()) {
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->withStatus(400);
    }

    $library->giveBackBookFromReservation($reservationId);

    $response = $response->withStatus(204);

    return $response;
})
    ->add(new AuthorizationMiddleware(['librarian']))
    ->add($authenticationMiddleware);

//List reservations for book
$app->map(['GET'], '/reservations', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) use ($library) {

    $bookId = $request->getParam('bookId');

    $content = [];
    if (isset($bookId)) {
        $content = $library->listReservationsForBook(Uuid::fromString($bookId));
    }

    $body = $response->getBody();
    $body->write(json_encode($content));

    $response = $response->withBody($body);
    $response = $response->withHeader('Content-Type', 'application/json');

    return $response;
})
    ->add(new AuthorizationMiddleware(['reader', 'librarian']))
    ->add($authenticationMiddleware);

return $app;
