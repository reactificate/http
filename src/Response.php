<?php


namespace Reactificate\Http;


use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Nette\Utils\Json;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\Message\Response as ReactResponse;
use React\Promise\Deferred;
use Reactificate\Http\Exceptions\ViewFileNotFound;
use Reactificate\Utils\Utils;
use RingCentral\Psr7\Stream;

class Response implements ResponseInterface
{
    /**
     * This event will be emitted when new data is written to the response stream
     */
    public const ON_WRITE = 'on.write';

    /**
     * This event will emitted when header is added to the response
     */
    public const ON_HEADERS = 'on.headers';

    /**
     * This event will be emitted before response is sent to the client
     */
    public const ON_BEFORE_SEND = 'on.before.send';

    /**
     * This event will be emitted when end() method is called
     */
    public const ON_END = 'on.end';

    /**
     * This event will be emitted when next handler is executed
     */
    public const ON_NEXT_HANDLER = 'on.next.middleware';


    protected const STREAM_FILE = 'php://temp';
    private static string $viewPath;
    protected array $headers = [];
    protected array $status = [
        'code' => 200,
        'phrase' => 'OK'
    ];
    protected string $version = '1.1';
    protected array $values = [
        'headers' => [],
    ];
    protected StreamInterface $stream;
    protected Handler $handler;
    protected ServerRequestInterface $request;
    protected EventEmitterInterface $event;
    private Deferred $deferred;
    private bool $isEnded = false;


    public function __construct(Deferred $deferred, ServerRequestInterface $request, array $handlers)
    {
        //Add event class to object storage
        Utils::set('reactificate.event', new EventEmitter());

        $this->deferred = $deferred;
        $this->handler = new Handler($handlers, $this);
        $this->request = $request;
    }

    /**
     * Set custom view directory
     *
     * @param string $viewPath
     */
    public static function setViewPath(string $viewPath): void
    {
        self::$viewPath = $viewPath;
    }

    public function handler(): Handler
    {
        return $this->handler;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    public function end($message = null): void
    {
        if ($this->isEnded) {
            return;
        }

        //emit before send event
        Utils::get('reactificate.event')
            ->emit(self::ON_BEFORE_SEND, [$message]);

        if (null !== $message) {
            if (!is_string($message)) {
                $message = Json::encode($message);
            }

            $this->getStream()->write($message);
        }

        $response = new ReactResponse(
            $this->status['code'],
            $this->headers,
            $this->stream,
            $this->version,
            $this->status['phrase']
        );

        //emit before send event
        Utils::get('reactificate.event')
            ->emit(self::ON_END, [$response]);

        $this->deferred->resolve($response);
        $this->isEnded = true;
    }

    protected function getStream(): StreamInterface
    {
        if (!isset($this->stream)) {
            $this->stream = new Stream(fopen(self::STREAM_FILE, 'w+'));
        }

        return $this->stream;
    }

    public function version(string $version): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        $this->version = $version;
        return $this;
    }

    public function on(string $eventName, callable $listener): ResponseInterface
    {
        Utils::get('reactificate.event')
            ->on($eventName, $listener);
        return $this;
    }

    public function once(string $eventName, callable $listener): ResponseInterface
    {
        Utils::get('reactificate.event')
            ->once($eventName, $listener);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function view(string $filePath, array $data = []): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        if (isset(self::$viewPath)) {
            if ('/' != substr(self::$viewPath, 0, 1)) {
                self::$viewPath .= DIRECTORY_SEPARATOR;
            }

            $filePath = self::$viewPath . $filePath;
        }

        if (!file_exists($filePath)) {
            throw new ViewFileNotFound("View file \"{$filePath}\" is not found.");
        }

        ob_start();

        extract($data);

        require $filePath;

        $html = ob_get_contents();

        ob_end_clean();

        return $this->html($html);
    }

    public function html(string $htmlCode): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        $this->header('Content-Type', 'text/html; charset=utf-8');
        return $this->write($htmlCode);
    }

    public function header($name, ?string $value = null): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        //Emit headers event
        Utils::get('reactificate.event')
            ->emit(self::ON_HEADERS, [$this->headers]);

        if (is_array($name)) {
            $this->headers = array_merge($this->headers, $name);
            return $this;
        }

        $this->headers[$name] = $value;
        return $this;
    }

    public function write($data): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        //emit write event
        Utils::get('reactificate.event')
            ->emit(self::ON_WRITE, [$data]);

        if (!is_scalar($data)) {
            return $this->json($data);
        }

        $this->getStream()->write($data);
        return $this;
    }

    public function json($arrayOrObject): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        $this->header('Content-Type', 'application/json; charset=utf-8');
        return $this->write(Json::encode($arrayOrObject));
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        if ($this->isEnded) {
            return;
        }

        $this->status($statusCode)
            ->header('Location', $url)
            ->html("Redirecting you to <a href=\"{$url}\">{$url}</a>...")
            ->end();
    }

    public function status(int $code, ?string $phrase = null): ResponseInterface
    {
        if ($this->isEnded) {
            return $this;
        }

        $this->status = [
            'code' => $code,
            'phrase' => $phrase
        ];

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnded(): bool
    {
        return $this->isEnded;
    }

    protected function createStream(string $body): StreamInterface
    {
        $stream = $this->getStream();
        $stream->write($body);
        $stream->rewind();
        return $stream;
    }
}