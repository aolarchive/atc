<?php

namespace Aol\Atc;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Action implements ActionInterface
{
	protected $allowed_formats = ['text/html', 'application/json', 'image/png'];
	protected $data = [];
	protected $http_code = 200;
	protected $view = '';

	/** @var array URL params */
	protected $params = [];

	public function __construct(array $params)
	{
		$this->params  = $params;
	}

	/**
	 * Takes a response object and returns an array of data. Formatting will be
	 * handled by a Presenter.
	 *
	 * @param Request $request
	 * @return Response|void
	 */
	abstract public function __invoke(Request $request);

	/**
	 * @return int
	 */
	public function getHttpCode()
	{
		return $this->http_code;
	}


	/**
	 * @inheritdoc
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * @inheritdoc
	 */
	public function getAllowedFormats()
	{
		return $this->allowed_formats;
	}

	/**
	 * @inheritdoc
	 */
	public function getData()
	{
		return $this->data;
	}
}
