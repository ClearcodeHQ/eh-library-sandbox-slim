<?php


namespace Clearcode\EHLibrarySandbox\Slim\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationMiddleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {

        $response = $next($request, $response);

        return $response;
    }
}
