<?php

require __DIR__.'/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new \Slim\App;

$app->get('/foo', function (ServerRequestInterface $request, ResponseInterface $response) {

    $response->write('Hello');

    return $response;
});

return $app;