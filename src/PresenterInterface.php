<?php

namespace Aol\Atc;

use Aura\Web\Response;

interface PresenterInterface
{
	/**
	 * @param array  $data
	 * @param string $format
	 * @param string $view
	 * @return string
	 */
	public function run($data, $format, $view = null);

	/**
	 * @return array
	 */
	public function getAvailableFormats();
}
