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
	 */
	public function __invoke(Response $response);

	/**
	 * Returns the allowed response formats. Will be used by the
	 * dispatcher to determine the correct response format.
	 *
	 * @see https://github.com/auraphp/Aura.Web/blob/develop-2/README-REQUEST.md#accept
	 *
	 * @return array
	 */
	public function getAllowedFormats();

	/**
	 * Returns the unformatted action data to be included in the response. The
	 * response format is required so that any further data pre-processing can
	 * happen before being sent to the presentation layer. For example, HTML
	 * responses may need to include specific template variables that would not
	 * be relevant in a JSON response.
	 *
	 * @param string $format
	 * @return array
	 */
	public function getData($format);

	/**
	 * Returns the view name.
	 *
	 * @return string
	 */
	public function getView();
}
