<?php


namespace Reactificate\Http\Middleware;


use Reactificate\Http\Handler;
use Reactificate\Http\ResponseInterface;

class Middleware extends Handler
{
    public function __construct(array $middlewares, ResponseInterface $response)
    {
        parent::__construct($middlewares, $response);
    }
}