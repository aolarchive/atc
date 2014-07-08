<?php

namespace Aol\Atc\Action;

use Aol\Atc\Action;
use Aura\Web\Response;

class ServerError extends Action
{
	protected $view = 'errors/500';

	/**
	 * @inheritdoc
	 */
	public function __invoke(Response $response)
	{
		$response->status->setCode(500);

		return $response;
	}
}
