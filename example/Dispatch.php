<?php

namespace Aol\AtcExample;

use Aura\Router\Router;

class Dispatch extends \Aol\Atc\Dispatch
{
	/**
	 * @inheritdoc
	 */
	protected function defineRoutes(Router $router)
	{
		$router->add('Index', '/test');
	}
}
