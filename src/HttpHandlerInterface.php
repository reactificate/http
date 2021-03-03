<?php


namespace Reactificate\Http;


use Nette\Utils\JsonException;
use Throwable;

interface HttpHandlerInterface
{
    /**
     * This serves like react's __invoke magic method,
     * it will be called when request reach this handler
     * @param ResponseInterface $response
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(ResponseInterface $response): void;
}