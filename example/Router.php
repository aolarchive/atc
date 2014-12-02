<?php

namespace Aol\AtcExample;

use Aura\Router\Generator;
use Aura\Router\RouteCollection;

class Router extends \Aura\Router\Router
{
	public function __construct(RouteCollection $routes, Generator $generator)
	{
		parent::__construct($routes, $generator);

		$this->addGet('Index', '/');
		$this->addGet('Redir', '/rip/');
	}
}
