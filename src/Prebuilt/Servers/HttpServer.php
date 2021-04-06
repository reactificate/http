<?php


namespace Reactificate\Http\Prebuilt\Servers;


use App\Http\Kernel;
use QuickRoute\Router\Collector;
use QuickRoute\Router\Dispatcher;
use Reactificate\Http\HttpHandlerInterface;
use Reactificate\Http\Middleware\Runner;
use Reactificate\Http\Prebuilt\Middleware\RoutingMiddleware;
use Reactificate\Http\ResponseInterface;
use Reactificate\Utils\Utils;

class HttpServer implements HttpHandlerInterface
{
    public function __construct()
    {
        $collector = Collector::create()
            ->collectFile(resources_path('routes/api.php'), [
                'prefix' => 'api',
                'name' => 'api'
            ])
            ->collectFile(resources_path('routes/web.php'))
            ->register();

        Utils::set('http.route.dispatcher', Dispatcher::create($collector));
        Utils::set('http.kernel', new Kernel());
    }

    public function handle(ResponseInterface $response): void
    {
        $kernel = new Kernel();
        $middlewareList = [];
        $middlewareList[] = new RoutingMiddleware();
        foreach ($kernel->middlewares as $middleware) {
            $middlewareList[] = new $middleware();
        }
        Runner::run($response, ...$middlewareList);
    }
}