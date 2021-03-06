<?php

require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/../src/ServerTiming.php";
require __DIR__ . "/../src/ServerTiming/StopWatch.php";

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Tuupola\Middleware\ServerTiming;
use Tuupola\Middleware\ServerTiming\Stopwatch;

$app = new \Slim\App;
$container = $app->getContainer();

$container["stopwatch"] = function ($container) {
    return new Stopwatch;
};

$container["ServerTiming"] = function ($container) {
    return new ServerTiming($container["stopwatch"]);
};

$container["DummyMiddleware"] = function ($container) {
    return function ($request, $response, $next) {
        usleep(200000);
        return $next($request, $response);
    };
};

$app->add("DummyMiddleware");
$app->add("ServerTiming");

$app->get("/test", function (Request $request, Response $response) {
    $this->stopwatch->start("External API");
    usleep(100000);
    $this->stopwatch->stop("External API");

    $this->stopwatch->closure("Magic", function () {
        usleep(50000);
    });

    $this->stopwatch->set("SQL", 34);

    return $response;
});

$app->run();
