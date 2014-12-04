<?php

namespace Aol\Atc\Events;

use Aol\Atc\ActionInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostDispatchEvent
 * @package Aol\Atc\Events
 */
class PostDispatchEvent extends Event
{
	/** @var Request */
	private $request;

	/** @var Response|null */
	private $response;

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param Request $request
	 * @param ActionInterface $action
	 * @param null $response
	 */
	public function __construct(Request $request, ActionInterface $action, $response = null)
	{
		$this->request  = $request;
		$this->action   = $action;
		$this->response = $response;
	}
} 