<?php

namespace Aol\Atc\Events;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DispatchErrorEvent
 * @package Aol\Atc\Events
 */
class DispatchErrorEvent extends Event
{
	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var \Exception
	 */
	private $exception;

	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param \Exception $exception
	 * @param Request $request
	 */
	function __construct(\Exception $exception, Request $request)
	{
		$this->exception = $exception;
		$this->request = $request;
	}
}