<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

$app = new \Slim\App;
$library = new \Clearcode\EHLibrary\Application();

$app->post('/books', function (ServerRequestInterface $request, ResponseInterface $response) use ($library, $app) {

    $bookId = Uuid::uuid4();

    $requestBody = $request->getParsedBody();

    if ($requestBody === null || !isset($requestBody['title']) || !isset($requestBody['authors']) || !isset($requestBody['isbn'])) {
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

return $app;