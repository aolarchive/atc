<?php

namespace Aol\Atc;

use Aura\Web\Request;

interface ActionFactoryInterface
{
	/**
	 * @param string  $action  Action name to be parsed.
	 * @param Request $request The request object.
	 * @param array   $params  An array of params from the router.
	 * @return ActionInterface|null
	 */
	public function newInstance($action, Request $request, $params);
}
