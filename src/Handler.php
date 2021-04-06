<?php


namespace Reactificate\Http;


use InvalidArgumentException;
use Reactificate\Http\Middleware\MiddlewareInterface;
use Reactificate\Utils\Utils;
use SplQueue;

/**
 * Class Handler
 * @package Reactificate\Http
 * @internal For internal use only
 */
class Handler
{
    /**
     * @var SplQueue<HttpHandlerInterface|MiddlewareInterface>
     */
    protected SplQueue $handlers;

    protected ResponseInterface $response;


    /**
     * Handler constructor.
     * @param array $handlers
     * @param ResponseInterface $response
     * @throws InvalidArgumentException
     */
    public function __construct(array $handlers, ResponseInterface $response)
    {
        $this->response = $response;
        $this->handlers = new SplQueue();
        foreach ($handlers as $handler) {
            //make sure that all handlers implement HttpHandlerInterface
            if (!$handler instanceof HttpHandlerInterface && !$handler instanceof MiddlewareInterface) {
                $strHandler = get_class($handler);
                throw new InvalidArgumentException("Handler {$strHandler} must implement Reactificate\Http\HttpHandlerInterface");
            }

            $this->handlers->push($handler);
        }

        $this->handlers->rewind();
    }

    public function next(): void
    {
        if ($this->response->isEnded()) {
            return;
        }

        //Emit next middleware event
        Utils::get('reactificate.event')
            ->emit(Response::ON_NEXT_HANDLER, []);

        $this->handlers->next();
        $current = $this->handlers->current();

        if ($current instanceof HttpHandlerInterface) { //run handler
            $current->handle($this->response);
        } elseif ($current instanceof MiddlewareInterface) { //run middleware
            /**@var \Reactificate\Http\Middleware\Middleware $this * */
            $current->run($this, $this->response);
        }
    }

    /**
     * @return  SplQueue<HttpHandlerInterface|MiddlewareInterface>
     */
    public function getQueue(): SplQueue
    {
        return $this->handlers;
    }
}