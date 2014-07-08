<?php

namespace Aol\Atc;

use Aura\Web\Response;

class Exception extends \Exception implements ActionInterface
{
	protected $http_code = 500;
	protected $view = 'errors/500';

	/**
	 * Takes a response, modifies it, and returns it back for processing. All
	 * data is expected to be assigned to the response content as an array.
	 * Formatting will be handled by a separate process.
	 *
	 * @param Response $response
	 * @return array
	 */
	public function __invoke(Response $response)
	{
		$response->status->setCode($this->http_code);

		return [];
	}

	/**
	 * Returns the view name.
	 *
	 * @return string
	 */
	public function getView()
	{
		return $this->view;
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
