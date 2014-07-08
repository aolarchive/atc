<?php

namespace Aol\Atc;

use Aura\Web\Response;

interface ActionInterface
{
	/**
	 * Takes a response object and returns an array of data. Formatting will be
	 * handled by a Presenter.
	 *
	 * @param Response $response
	 * @return array
	 */
	public function __invoke(Response $response);

	/**
	 * Returns the view name.
	 *
	 * @return string
	 */
	public function getView();

	/**
	 * Returns the allowed response formats. Will be used by the
	 * dispatcher to determine the correct response format.
	 *
	 * @see https://github.com/auraphp/Aura.Web/blob/develop-2/README-REQUEST.md#accept
	 *
	 * @return array
	 */
	public function getAllowedFormats();
}
