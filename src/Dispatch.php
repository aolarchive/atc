<?php

namespace Aol\Atc;

use Aol\Atc\EventHandlers\EventHandlerInterface;
use Aol\Atc\Events\DispatchErrorEvent;
use Aol\Atc\Events\PostDispatchEvent;
use Aol\Atc\Events\PostPresentEvent;
use Aol\Atc\Events\PreDispatchEvent;
use Aol\Atc\Events\PrePresentEvent;
use Aol\Atc\Exceptions\ActionNotFoundException;
use Aol\Atc\Exceptions\ExitDispatchException;
use Aol\Atc\Exceptions\PageNotFoundException;
use Aura\Accept\AcceptFactory;
use Aura\Router\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Dispatch
 *
 * @package Aol\Atc
 */
class Dispatch
{
	/** @var \Aol\Atc\ActionFactoryInterface */
	private $action_factory;

	/** @var \Aol\Atc\PresenterInterface */
	private $presenter;

	/** @var \Aura\Router\Router */
	private $router;

	/** @var bool */
	private $debug_enabled = false;

	/** @var Request */
	private $request;

	/** @var \Aura\Router\Route */
	private $matched_route;

	/** @var EventDispatcherInterface */
	private $events;

	/** @var ActionInterface */
	private $action;

	/**
	 * @param Router $router
	 * @param Request $request
	 * @param ActionFactoryInterface $action_factory
	 * @param PresenterInterface $presenter
	 * @param EventDispatcherInterface $event_dispatcher
	 * @param EventHandlerInterface $exception_handler
	 */
	public function __construct(
		Router $router,
		Request $request,
		ActionFactoryInterface $action_factory,
		PresenterInterface $presenter,
		EventDispatcherInterface $event_dispatcher,
		EventHandlerInterface $exception_handler
	) {
		$this->router           = $router;
		$this->request          = $request;
		$this->action_factory   = $action_factory;
		$this->presenter        = $presenter;
		$this->events = $event_dispatcher;

		$this->events->addListener(DispatchEvents::DISPATCH_ERROR, $exception_handler, DispatchEvents::LATE_EVENT);
	}

	/**
	 * Dispatch to an action and handle errors
	 *
	 * @throws ExitDispatchException
	 */
	public function run()
	{
		$this->action = $this->getAction($this->request);
		return $this->process();
	}

	protected function process() {
		// --------------- Dispatch
		$response = $this->dispatch($this->request);

		// --------------- Present
		if (!($response instanceof Response)) {
			$response = $this->present($response);
		}

		// --------------- Return
		return $response;
	}

	/**
	 * @param Request 		  $request
	 * @param bool 			  $dispatch
	 * @throws ExitDispatchException
	 * @throws \Exception
	 * @return mixed
	 */
	protected function dispatch(Request $request, $dispatch = true)
	{
		$response = null;
		$action  = $this->action;
		try {
			$dispatch && $this->events->dispatch(DispatchEvents::PRE_DISPATCH, new PreDispatchEvent($this->request, $action));
			$response = $action($request);
			$dispatch && $this->events->dispatch(DispatchEvents::POST_DISPATCH, new PostDispatchEvent($this->request, $action, $response));
		} catch (ActionInterface $exc) { // Re-dispatch if the exception implements ActionInterface (http://i.imgur.com/QKIfg.gif)
			$this->action = $exc;
			$response = $this->dispatch($request, false);	// Re-Dispatch without events
		} catch (\Exception $exc) {
			$dispatch && $this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $request));
			if (!$dispatch) {
				throw $exc;
			}
		}
		return $response;
	}

	/**
	 * @param $data
	 * @throws Exception
	 * @throws \Exception
	 * @return Response
	 */
	protected function present($data)
	{
		$media_type = $this->getMedia($this->action)->getValue();

		try {
			$pre_present_event = new PrePresentEvent($this->request, $this->action, $data);
			$this->events->dispatch(DispatchEvents::PRE_PRESENT, $pre_present_event);
			$data = $pre_present_event->getData();

			$response = $this->presenter->run($data, $media_type, $this->action->getView());
			$response->setStatusCode($this->action->getHttpCode());

			$this->events->dispatch(DispatchEvents::POST_PRESENT, new PostPresentEvent($this->request, $response, $this->action));
		} catch(Exception $e) {
			$this->action = $e;
			$response = $this->process();
		} catch (\Exception $exc) {
			$this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $this->request, $this->debug_enabled));
			$this->action = new Exception('Unknown presentation error');
			$response = $this->process();
		}

		return $response;
	}

	/**
	 * Return true or false if the target route is defined in the router
	 *
	 * @return bool
	 */
	public function routeExists()
	{
		if (!isset($this->matched_route)) {
			$this->matched_route = $this->matchRoute();
		}

		return (bool)$this->matched_route;
	}

	/**
	 * Toggle debugging on
	 */
	public function enableDebug()
	{
		$this->debug_enabled = true;
	}

	/**
	 * Toggle debugging off
	 */
	public function disableDebug()
	{
		$this->debug_enabled = false;
	}

	/**
	 * Searches for the target route on the router, returning it or false if it does not exist.
	 *
	 * @return \Aura\Router\Route|false
	 */
	protected function matchRoute()
	{
		$this->matched_route = $this->router->match(
			$this->request->getPathInfo(),
			$this->request->server->all()
		);

		return $this->matched_route;
	}

	/**
	 * Get the media type for the request
	 *
	 * @param ActionInterface $action
	 * @return \Aura\Accept\Media\MediaValue|false
	 * @throws Exception
	 */
	protected function getMedia(ActionInterface $action)
	{
		$available = array_intersect($action->getAllowedFormats(), $this->presenter->getAvailableFormats());

		//@todo don't mix Di and randomly calling factories
		$accept_factory = new AcceptFactory($_SERVER);
		$accept         = $accept_factory->newInstance();
		$media = $accept->negotiateMedia($available);
		if (empty($media)) {
			throw new Exception('Could not find a compatible content type for response');
		}

		return $media;
	}

	/**
	 * Attempt to match a route and instantiate an Action, bailing out with an exception on failure
	 *
	 * @param Request $request
	 * @return ActionInterface
	 * @throws ActionNotFoundException
	 * @throws PageNotFoundException
	 */
	protected function getAction(Request $request)
	{
		// Get the matched route.
		$route = $this->matched_route ?: $this->matchRoute();
		if (!$route) {
			$exc = new PageNotFoundException();
			$this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $request));
			return $exc;
		}

		$params = $route->params;

		// Get the appropriate action.
		$action = $this->action_factory->newInstance($params['action'], $params);
		if (!$action) {
			$exc = new ActionNotFoundException('Action not found: ' . $params['action']);
			$this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $request));
			return $exc;
		}

		return $action;
	}
}
