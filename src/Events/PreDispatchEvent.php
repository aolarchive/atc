<?php

namespace Aol\Atc\Events;

use Aol\Atc\ActionInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PreDispatchEvent
 * @package Aol\Atc\Events
 */
class PreDispatchEvent extends Event
{
	/** @var Request */
	private $request;

	/** @var ActionInterface */
	private $action;

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return ActionInterface
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @param Request $request
	 * @param ActionInterface $action
	 */
	public function __construct(Request $request, ActionInterface $action)
	{
		$this->request = $request;
		$this->action  = $action;
	}
} 