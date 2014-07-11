<?php

namespace Aol\Atc;

use Aura\Web\Request;

class ActionFactory implements ActionFactoryInterface
{
	protected $namespace = '';

	public function __construct($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * @inheritdoc
	 */
	public function newInstance($action, Request $request, $params)
	{
		$class = $this->parseAction($action);
		if (!is_null($class)) {
			$class = new $class($request, $params, $this->router);
		}

		return new $class($request, $params);
	}

	/**
	 * @param string $action
	 * @return string
	 */
	protected function parseAction($action)
	{
		$class = $this->namespace . str_replace('.', '\\', $action);
		$class = class_exists($class) ? $class : null;

		return $class;
	}
}
