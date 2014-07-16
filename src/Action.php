<?php

namespace Aol\Atc;

use Aura\Web\Request;
use Aura\Web\Response;

abstract class Action implements ActionInterface
{
	protected $allowed_formats = ['text/html', 'application/json', 'image/png'];
	protected $data = [];
	protected $view = '';

	/** @var array URL params */
	private $params = [];

	/** @var Request Request object */
	private $request;

	public function __construct(Request $request, array $params)
	{
		$this->request = $request;
		$this->params  = $params;
	}

	/**
	 * @inheritdoc
	 */
	abstract public function __invoke(Response $response);

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
	public function getData($format)
	{
		return $this->data;
	}

	protected function getRequest()
	{
		return $this->request;
	}

	protected function getParams()
	{
		return $this->params;
	}
}
