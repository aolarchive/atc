<?php

namespace Aol\Atc;

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

	/**
	 * @param Router $router
	 * @param Request $request
	 * @param ActionFactoryInterface $action_factory
	 * @param PresenterInterface $presenter
	 * @param EventDispatcherInterface $event_dispatcher
	 */
	public function __construct(
		Router $router,
		Request $request,
		ActionFactoryInterface $action_factory,
		PresenterInterface $presenter,
		EventDispatcherInterface $event_dispatcher
	) {
		$this->router           = $router;
		$this->request          = $request;
		$this->action_factory   = $action_factory;
		$this->presenter        = $presenter;
		$this->events = $event_dispatcher;
	}

	/**
	 * Dispatch to an action and handle errors
	 *
	 * @throws ExitDispatchException
	 */
	public function run()
	{
		// --------------- Dispatch
		$action = $this->getAction($this->request);
		$response = $this->dispatch($action, $this->request);

		// If action returns nothing, try to extract data that may have been set on its internal data storage
		if (is_null($response)) {
			$response = $action->getData();
		}

		// --------------- Present
		if (!($response instanceof Response)) {
			$response = $this->present($action, $response);
		}

		// --------------- Return
		return $response;
	}

	/**
	 * @param ActionInterface $action
	 * @param Request 		  $request
	 * @param bool 			  $dispatch
	 * @throws ExitDispatchException
	 * @throws \Exception
	 * @return mixed
	 */
	protected function dispatch(ActionInterface $action, Request $request, $dispatch = true)
	{
		$response = null;
		$dispatch && $this->events->dispatch(DispatchEvents::PRE_DISPATCH, new PreDispatchEvent($this->request, $action));
		try {
			$response = $action($request);
		} catch (ActionInterface $exc) { // Re-dispatch if the exception implements ActionInterface (http://i.imgur.com/QKIfg.gif)
			$response = $this->dispatch($exc, $request, false);	// Re-Dispatch without events
		} catch (\Exception $exc) {
			$dispatch && $this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $request));
			throw $exc;
		}
		$dispatch && $this->events->dispatch(DispatchEvents::POST_DISPATCH, new PostDispatchEvent($this->request, $action, $response));
		return $response;
	}

	/**
	 * @param ActionInterface $action
	 * @param $data
	 * @return Response
	 */
	protected function present(ActionInterface $action, $data)
	{
		$media_type = $this->getMedia($action)->getValue();

		$this->events->dispatch(DispatchEvents::PRE_PRESENT, new PrePresentEvent($this->request, $action));
		try {
			$response = $this->presenter->run($data, $media_type, $action->getView());
			$response->setStatusCode($action->getHttpCode());
		} catch (\Exception $exc) {
			$this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $this->request));
			$content = $this->debug_enabled ? $exc->getMessage() : $this->getErrorHtmlResponse();
			$response = new Response($content, Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'text/html']);
		}
		$this->events->dispatch(DispatchEvents::POST_PRESENT, new PostPresentEvent($this->request, $response, $action));

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

	public function enableDebug()
	{
		$this->debug_enabled = true;
	}

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
			throw $exc;
		}

		$params = $route->params;

		// Get the appropriate action.
		$action = $this->action_factory->newInstance($params['action'], $params);
		if (!$action) {
			$exc = new ActionNotFoundException();
			$this->events->dispatch(DispatchEvents::DISPATCH_ERROR, new DispatchErrorEvent($exc, $request));
			throw $exc;
		}

		return $action;
	}

	/**
	 * Returns a string to be sent to the browser in the event everything falls
	 * to pieces. This is implemented in a protected method so that children
	 * can override this behavior as needed.
	 *
	 * @return string
	 */
	protected function getErrorHtmlResponse()
	{
		return '<html><head><title>Oops! Something went wrong.</title></head><body>Oops! Looks like something went wrong.</body></html>';
	}
}
