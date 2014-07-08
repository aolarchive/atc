<?php

namespace Aol\Atc;

use Aura\Web\Request;

class ActionFactory implements ActionFactoryInterface
{
	private $namespace = '';

	public function __construct($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * @param string  $action  Action name to be parsed.
	 * @param Request $request The request object.
	 * @param array   $params  An array of params from the router.
	 * @return ActionInterface|null
	 */
	public function newInstance($action, Request $request, $params)
	{
		$class = $this->namespace . str_replace('.', '\\', $action);

		return new $class($request, $params);
	}
}
