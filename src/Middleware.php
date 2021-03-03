<?php


namespace Reactificate\Http;


use InvalidArgumentException;
use Nette\Utils\JsonException;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Throwable;

class Middleware
{
    protected array $httpHandlers;


    /**
     * @param ServerRequestInterface $request
     * @return PromiseInterface
     * @throws JsonException
     * @throws InvalidArgumentException|Throwable
     */
    public function __invoke(ServerRequestInterface $request): PromiseInterface
    {
        $deferred = new Deferred();
        $promise = $deferred->promise();
        $response = new Response($deferred, $request, $this->httpHandlers);

        $handler = $this->httpHandlers[0];

        if (!$handler instanceof HttpHandlerInterface) {
            $handlerStr = get_class($handler);
            throw new InvalidArgumentException("Handler {$handlerStr} must implement Reactificate\Http\HandlerInterface");
        }

        $handler->handle($response);
        return $promise;
    }

    public function handler(HttpHandlerInterface ...$httpHandlerInterfaces): void
    {
        $this->httpHandlers = $httpHandlerInterfaces;
    }
}