<?php

namespace Aol\Atc;

use Aura\Web\Response;

class Presenter implements PresenterInterface
{
	/** @var string View directory. */
	private $view_dir;

	/** @var string View extension. */
	private $view_ext = '.php';

	/**
	 * @param string $view_dir View directory
	 */
	public function __construct($view_dir)
	{
		$this->view_dir = $view_dir;
	}

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
			default:
				$file = $this->view_dir . $view . $this->view_ext;
				if (!file_exists($file)) {
					throw new Exception('View does not exist: ' . $view);
				}

				ob_start();
				require $file;
				$data = ob_get_clean();
		}

		return $data;
	}
}
