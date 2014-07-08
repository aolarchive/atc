<?php

namespace Aol\AtcExample\Actions;

use Aol\Atc\Action;
use Aura\Web\Response;

class Index extends Action
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(Response $response)
	{
		return ['name' => 'Ralph'];
	}
}
