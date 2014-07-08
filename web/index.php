<?php

require __DIR__ . '/../vendor/autoload.php';

$router_factory = new \Aura\Router\RouterFactory();


$dispatch = new \Aol\AtcExample\Dispatch(
	$router_factory->newInstance(),
	new \Aura\Web\WebFactory($GLOBALS),
	new \Aol\Atc\ActionFactory('Aol\\AtcExample\\Actions\\'),
	new \Aol\Atc\Presenter(),
	new \Psr\Log\NullLogger()
);

$dispatch->run();
