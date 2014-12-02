<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

$router_factory = new \Aura\Router\RouterFactory();

$router = new \Aol\AtcExample\Router(
	new \Aura\Router\RouteCollection(new \Aura\Router\RouteFactory()),
	new \Aura\Router\Generator()
);

$dispatch = new \Aol\Atc\Dispatch(
	$router,
	Request::createFromGlobals(),
	new \Aol\Atc\ActionFactory('Aol\\AtcExample\\Actions\\'),
	new \Aol\Atc\Presenter(__DIR__ . '/../resources/views/')
);

$dispatch->enableDebug();
$response = $dispatch->run();
$response->send();
