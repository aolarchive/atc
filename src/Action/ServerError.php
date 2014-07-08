<?php

namespace Aol\Atc\Action;

use Aol\Atc\ActionInterface;
use Aura\Web\Response;

class ServerError implements ActionInterface
{
	/**
	 * Takes a response, modifies it, and returns it back for processing. All
	 * data is expected to be assigned to the response content as an array.
	 * Formatting will be handled by a separate process.
	 *
	 * @param Response $response
	 * @return Response
	 */
	public function __invoke(Response $response)
	{
		$response->status->setCode(500);

		return $response;
	}

	/**
	 * Returns the view name.
	 *
	 * @return string
	 */
	public function getView()
	{
		return 'errors/500';
	}

	/**
	 * Returns the allowed response formats. Will be used by the
	 * dispatcher to determine the correct response format.
	 *
	 * @see https://github.com/auraphp/Aura.Web/blob/develop-2/README-REQUEST.md#accept
	 *
	 * @return array
	 */
	public function getAllowedFormats()
	{
		return ['text/html', 'application/json'];
	}
}
