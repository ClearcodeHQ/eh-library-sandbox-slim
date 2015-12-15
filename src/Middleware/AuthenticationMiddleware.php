<?php


namespace Clearcode\EHLibrarySandbox\Slim\Middleware;


use Clearcode\EHLibraryAuth\Model\User;
use Clearcode\EHLibraryAuth\Model\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware
{

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        /* your code here */
        /* authenticate user using JWT token passed in the request */

        $email = null; /* assign email here */

        $user = $this->repository->get($email);
        $isAuthenticated = $user instanceof User;

        if ($isAuthenticated) {
            $request = $request->withAttribute('user', $user);
            $response = $next($request, $response);
        } else {
            /* your code here */
            /* handle case where user is not authenticated */
        }

        return $response;
    }
}
