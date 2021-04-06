<?php


namespace Reactificate\Http\Prebuilt\Middleware;


use Closure;
use Reactificate\Http\Middleware\Middleware;
use Reactificate\Http\Middleware\MiddlewareInterface;
use Reactificate\Http\ResponseInterface;

class ControllerMiddleware implements MiddlewareInterface
{
    private Closure $controllerClosure;

    public function __construct(Closure $closure)
    {
        $this->controllerClosure = $closure;
    }

    /**
     * @inheritDoc
     */
    public function run(Middleware $middleware, ResponseInterface $response): void
    {
        $controllerClosure = $this->controllerClosure;
        $controllerClosure();
    }
}