<?php


namespace Clearcode\EHLibrarySandbox\Slim\Middleware;


use Clearcode\EHLibraryAuth\LibraryAuth;
use Clearcode\EHLibraryAuth\Model\User;
use Lcobucci\JWT\Parser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware
{
    /** @var LibraryAuth */
    private $auth;

    public function __construct(LibraryAuth $auth)
    {
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $token = $request->getHeaderLine('XAuthorization'); // because Authorization is cutted for some reason

        /* your code here */

        $this->auth->authenticate($token);

        /* your code here */

        $token = (new Parser())->parse($token);

        $user = $this->auth->getUser($token->getClaim('email'));

        if (!$user instanceof User) {
            /* your code here */
        }

        $request = $request->withAttribute('user', $user);
        $response = $next($request, $response);

        return $response;
    }
}
