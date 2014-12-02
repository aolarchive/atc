<?php

namespace Aol\Atc;

use Symfony\Component\HttpFoundation\Response;

interface PresenterInterface
{
	/**
	 * @param array  $data
	 * @param string $format
	 * @param string $view
	 * @return Response
	 */
	public function run($data, $format, $view = null);

	/**
	 * Returns an array of media types that the presenter supports
	 *
	 * @return string[]
	 */
	public function getAvailableFormats();
}
