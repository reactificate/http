<?php


namespace Reactificate\Http;


use InvalidArgumentException;
use Reactificate\ObjectStorage;
use SplQueue;

/**
 * Class Handler
 * @package Reactificate\Http
 * @internal For internal use only
 */
class Handler
{
    /**
     * @var SplQueue<HttpHandlerInterface>
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
            if (!$handler instanceof HttpHandlerInterface) {
                $strHandler = get_class($handler);
                throw new InvalidArgumentException("Handler {$strHandler} must implement Reactificate\Http\HandlerInterface");
            }

            $this->handlers->push($handler);
        }

        $this->handlers->rewind();
    }

    public function next(): void
    {
        //Emit next middleware event
        ObjectStorage::get('Reactificate.event')
            ->emit(Response::ON_NEXT_HANDLER, []);

        $this->handlers->next();
        $this->handlers->current()->handle($this->response);
    }

    /**
     * @return  SplQueue<HttpHandlerInterface>
     */
    public function getQueue(): SplQueue
    {
        return $this->handlers;
    }
}