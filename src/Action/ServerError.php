<?php

namespace Aol\Atc\Action;

use Aol\Atc\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerError extends Action
{
	protected $view = 'errors/500';

	/**
	 * @inheritdoc
	 */
	public function __invoke(Request $request)
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		return $response;
	}
}
