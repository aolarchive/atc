<?php

namespace Aol\Atc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Exception extends \Exception implements ActionInterface
{
	protected $data = ['status' => 'error', 'message' => 'system error'];
	protected $http_code = 500;
	protected $view = 'errors/500';

	/**
	 * Takes a response, modifies it, and returns it back for processing. All
	 * data is expected to be assigned to the response content as an array.
	 * Formatting will be handled by a separate process.
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function __invoke(Request $request)
	{
		$message = $this->getMessage();
		if (!empty($message)) {
			$this->data['message'] = $message;
		}

		return $this->data;
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
		return ['text/html', 'application/json', 'image/x-icon'];
	}

	/**
	 * Returns the unformatted action data to be included in the response.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getHttpCode()
	{
		return $this->http_code;
	}
}
