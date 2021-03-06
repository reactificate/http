<?php


namespace Reactificate\Http;


use Nette\Utils\JsonException;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseInterface
{
    /**
     * Get http server request
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface;

    /**
     * Process next handler in the queue
     * @return Handler
     */
    public function handler(): Handler;

    /**
     * Append data to response
     *
     * @param mixed $data array or object
     * @return $this
     * @throws JsonException
     */
    public function write($data): ResponseInterface;

    /**
     * Respond with json encoded data
     *
     * @param array|object $arrayOrObject array or object
     * @return $this
     * @throws JsonException
     */
    public function json($arrayOrObject): ResponseInterface;

    /**
     * Respond with html code
     *
     * @param string $htmlCode array or object
     * @return $this
     */
    public function html(string $htmlCode): ResponseInterface;

    /**
     * Set http status code
     *
     * @param int $code http status code response
     * @param string|null $phrase http status phrase response
     * @return ResponseInterface
     */
    public function status(int $code, ?string $phrase = null): ResponseInterface;

    /**
     * Add header to response
     *
     * @param string|array $name
     * @param string|null $value
     * @return ResponseInterface
     */
    public function header($name, ?string $value = null): ResponseInterface;

    /**
     * Http protocol version
     *
     * @param string $version
     * @return ResponseInterface
     */
    public function version(string $version): ResponseInterface;

    /**
     * Terminate request and respond with this class
     *
     * @param string|array|object|null $message
     * @throws JsonException
     */
    public function end($message = null): void;

    /**
     * Listens to response event
     *
     * @param string $eventName
     * @param callable $listener
     * @return ResponseInterface
     */
    public function on(string $eventName, callable $listener): ResponseInterface;

    /**
     * Listens to response once event
     *
     * @param string $eventName
     * @param callable $listener
     * @return ResponseInterface
     */
    public function once(string $eventName, callable $listener): ResponseInterface;

    /**
     * Send response with view file
     *
     * @param string $filePath
     * @param array $data
     * @return ResponseInterface
     */
    public function view(string $filePath, array $data = []): ResponseInterface;

    /**
     * Redirect browser to another webpage
     *
     * @param string $url
     * @param int $statusCode
     * @throws JsonException
     */
    public function redirect(string $url, int $statusCode = 302): void;

    /**
     * Check whether response is ended
     *
     * @return bool
     */
    public function isEnded(): bool;
}