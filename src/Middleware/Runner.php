<?php


namespace Reactificate\Http\Middleware;


use Reactificate\Http\ResponseInterface;

class Runner
{
    public static function run(ResponseInterface $response, MiddlewareInterface ...$middlewares): void
    {
        $middleware = new Middleware($middlewares, $response);
        $middleware->getQueue()->current()->run($middleware, $response);
    }
}