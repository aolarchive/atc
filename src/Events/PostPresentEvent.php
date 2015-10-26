<?php

namespace Aol\Atc\Events;

use Aol\Atc\ActionInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostPresentEvent
 * @package Aol\Atc\Events
 */
class PostPresentEvent extends Event
{
	/** @var ActionInterface */
	private $action;

	/** @var Request */
	private $request;

	/** @var Response */
	private $response;

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param ActionInterface $action
	 */
	public function __construct(Request $request, Response $response, ActionInterface $action)
	{
		$this->request  = $request;
		$this->response = $response;
		$this->action   = $action;
	}

	/**
	 * @return ActionInterface
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
