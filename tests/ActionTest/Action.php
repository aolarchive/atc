<?php

namespace Aol\Atc\Tests\ActionTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Action extends \Aol\Atc\Action
{
	public function __invoke(Request $request)
	{
	}

	public function getParams()
	{
		return $this->params;
	}
}
