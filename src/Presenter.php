<?php

namespace Aol\Atc;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

	/**
	 * @inheritdoc
	 */
	public function getAvailableFormats()
	{
		return ['text/html', 'application/json', 'image/png'];
	}

	/**
	 * @inheritdoc
	 */
	public function run($data, $format, $view = null)
	{
		switch ($format) {
			case 'application/json':
				$response = new JsonResponse($data);
				break;

			case 'image/gif':
			case 'image/jpeg':
			case 'image/png':
				$response = new BinaryFileResponse($data);
				break;

			case 'text/html':
			default:
				$file = $this->view_dir . $view . $this->view_ext;
				if (!file_exists($file)) {
					throw new Exception('View does not exist: ' . $view);
				}

				ob_start();
				require $file;
				$response = new Response(ob_get_clean());
		}

		return $response;
	}
}
