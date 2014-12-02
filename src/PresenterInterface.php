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
	 * @return array
	 */
	public function getAvailableFormats();
}
