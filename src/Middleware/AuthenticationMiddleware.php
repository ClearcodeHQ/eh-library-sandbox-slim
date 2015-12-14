<?php


namespace Clearcode\EHLibrarySandbox\Slim\Middleware;


use Clearcode\EHLibrary\Library;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware
{

    /**
     * @var Library
     */
    private $library;

    public function __construct(Library $library)
    {
        $this->library = $library;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next) {

        /* your code here */

        $user = null; /* assign user here */

        $request = $request->withAttribute('user', $user);
        $response = $next($request, $response);

        return $response;
    }
}