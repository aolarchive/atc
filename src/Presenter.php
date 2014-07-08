<?php

namespace Aol\Atc;

use Aura\Web\Response;

class Presenter implements PresenterInterface
{
	public function getAvailableFormats()
	{
		return ['text/html', 'application/json'];
	}

	/**
	 * @inheritdoc
	 */
	public function run($data, $format, $view = null)
	{
		switch ($format) {
			case 'application/json':
				$data = json_encode($data);
				break;

			case 'text/html':
				$data = var_export($data, true);
		}

		return $data;
	}
}
