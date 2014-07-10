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
	 * @inheritdoc
	 */
	public function newInstance($action, Request $request, $params)
	{
		$class = $this->parseAction($action);

		return new $class($request, $params);
	}

	/**
	 * @param string $action
	 * @return string
	 */
	protected function parseAction($action)
	{
		return $this->namespace . str_replace('.', '\\', $action);
	}
}
