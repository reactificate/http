<?php


namespace Reactificate\Http\Prebuilt\Middleware;


use App\Http\Kernel;
use QuickRoute\Router\Dispatcher;
use Reactificate\Http\Middleware\Middleware;
use Reactificate\Http\Middleware\MiddlewareInterface;
use Reactificate\Http\Middleware\Runner;
use Reactificate\Http\ResponseInterface;
use Reactificate\Utils\Utils;

class RoutingMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function run(Middleware $middleware, ResponseInterface $response): void
    {
        $request = $response->request();

        /**@var Dispatcher $dispatcher * */
        $dispatcher = Utils::get('http.route.dispatcher');

        /**@var Kernel $kernel * */
        $kernel = Utils::get('http.kernel');

        $dispatchResult = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch (true) {
            case $dispatchResult->isFound():
                $route = $dispatchResult->getRoute();
                $controller = $route->getHandler()[0];
                $method = $route->getHandler()[1];
                if (!empty($route->getMiddleware())) {
                    $middlewareList = [];
                    foreach ($route->getMiddleware() as $middlewareName) {
                        if (array_key_exists($middlewareName, $kernel->routingMiddlewares)) {
                            $middlewareList[] = new $kernel->routingMiddlewares[$middlewareName];
                        }
                    }

                    //Run controller class as middleware
                    $middlewareList[] = new ControllerMiddleware(function () use ($controller, $method, $response) {
                        (new $controller())->$method($response);
                    });

                    Runner::run($response, ...$middlewareList);
                    return;
                }

                (new $controller())->$method($response);
                break;
            case $dispatchResult->isNotFound():
                $response->html('Not Found ---> ');
                $response->html($response->request()->getUri())->end();
                break;
            case $dispatchResult->isMethodNotAllowed():
                $response->end('Method Not Allowed ---> ');
                $response->html(
                    $response->request()->getUri()
                    . '@'
                    . $response->request()->getMethod()
                )->end();
                break;
            default:
                $response->end('Internal Server Error :)');
        }
    }
}