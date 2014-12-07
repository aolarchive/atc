<?php

namespace Aol\Atc\Events;

use Aol\Atc\ActionInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PrePresentEvent
 *
 * @package Aol\Atc\Events
 */
class PrePresentEvent extends Event
{
	/** @var ActionInterface */
	private $action;
	/** @var mixed Presentation data */
	private $data;
	/** @var Request */
	private $request;

	/**
	 * @param Request         $request
	 * @param ActionInterface $action
	 * @param mixed           $data
	 */
	public function __construct(Request $request, ActionInterface $action, $data)
	{
		$this->request = $request;
		$this->action  = $action;
		$this->data    = $data;
	}

	/**
	 * @return ActionInterface
	 */
	public function getAction()
	{
		return $this->action;
	}

	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	public function setData($data)
	{
		$this->data = $data;
	}
}
