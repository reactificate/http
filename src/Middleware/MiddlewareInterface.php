<?php


namespace Reactificate\Http\Middleware;


use Reactificate\Http\ResponseInterface;

interface MiddlewareInterface
{
    /**
     * Run middleware
     *
     * @param Middleware $middleware
     * @param ResponseInterface $response
     */
    public function run(Middleware $middleware, ResponseInterface $response): void;
}