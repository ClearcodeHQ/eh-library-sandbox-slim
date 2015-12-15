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

        $email = null; /* assign email here */

        $user = $this->repository->get($email);

        if ($user instanceof User) {
            $request = $request->withAttribute('user', $user);
            $response = $next($request, $response);
        } else {
            /* your code here */
        }

        return $response;
    }
}
