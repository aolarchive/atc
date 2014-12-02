<?php

namespace Aol\Atc;

interface ActionFactoryInterface
{
	/**
	 * @param string  $action  Action name to be parsed.
	 * @param array   $params  An array of params from the router.
	 * @return ActionInterface|null
	 */
	public function newInstance($action, $params);
}
