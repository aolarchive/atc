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
	 * @var bool
	 */
	private $debug;

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
	 * @return boolean
	 */
	public function isDebug()
	{
		return $this->debug;
	}

	/**
	 * @param \Exception $exception
	 * @param Request $request
	 * @param bool $debug
	 */
	function __construct(\Exception $exception, Request $request, $debug = false)
	{
		$this->exception = $exception;
		$this->request = $request;
		$this->debug = $debug;
	}
}