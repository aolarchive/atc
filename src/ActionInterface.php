<?php

namespace Aol\Atc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ActionInterface
{
	/**
	 * Takes a response object and returns an array of data. Formatting will be
	 * handled by a Presenter.
	 *
	 * @param Request $request
	 * @return Response|void
	 */
	public function __invoke(Request $request);

	/**
	 * This method is expected to be run after the action is invoked.
	 *
	 * @param Request  $request
	 * @param Response $response
	 * @return Response
	 */
	public function after(Request $request, Response $response = null);

	/**
	 * This method is expected to be run before the action is invoked.
	 *
	 * @param Request $request
	 */
	public function before(Request $request);

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
	 * Returns the unformatted action data to be included in the response.
	 *
	 * @return array
	 */
	public function getData();

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
