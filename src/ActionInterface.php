<?php

namespace Aol\Atc;

use Symfony\Component\HttpFoundation\Request;

interface ActionInterface
{
	/**
	 * Takes a response object and returns an array of data. Formatting will be
	 * handled by a Presenter.
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function __invoke(Request $request);

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
	 * @return int
	 */
	public function getHttpCode();

	/**
	 * Returns the view name.
	 *
	 * @return string
	 */
	public function getView();
}
