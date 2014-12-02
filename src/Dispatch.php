<?php

namespace Aol\Atc;

use Aol\Atc\Exceptions\ActionNotFoundException;
use Aol\Atc\Exceptions\ExitDispatchException;
use Aol\Atc\Exceptions\PageNotFoundException;
use Aura\Accept\AcceptFactory;
use Aura\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

	/**
	 * @param Router                 $router
	 * @param Request                $request
	 * @param ActionFactoryInterface $action_factory
	 * @param PresenterInterface     $presenter
	 */
	public function __construct(
		Router $router,
		Request $request,
		ActionFactoryInterface $action_factory,
		PresenterInterface $presenter
	) {
		$this->router         = $router;
		$this->request        = $request;
		$this->action_factory = $action_factory;
		$this->presenter      = $presenter;
	}

	/**
	 * Dispatch to an action and handle errors
	 *
	 * @throws ExitDispatchException
	 */
	public function run()
	{
		// --------------- Dispatch
		try {
			$action   = $this->getAction($this->request);
			$response = $this->dispatch($action);
		} catch (\Exception $e) {
			// This is your escape hatch.
			if ($e instanceof ExitDispatchException) {
				throw $e;
			}

			// Exceptions can implement the action interface to handle themselves. For anything else, use the default
			$action   = ($e instanceof ActionInterface) ? $e : new Exception($e->getMessage());
			$response = $this->dispatch($action);
		}

		// --------------- Present
		if (!($response instanceof Response)) {
			try {
//				echo $this->getMedia($action);
				$response = $this->presenter->run(
					$action->getData(),
					$this->getMedia($action)->getValue(),
					$action->getView()
				);
				$response->setStatusCode($action->getHttpCode());
			} catch (\Exception $e) {
				$content  = $this->debug_enabled ? $e->getMessage() : $this->errorHtmlResponse();
				$response = new Response($content, 500, array('Content-Type' => 'text/html'));
			}
		}

		// --------------- Return
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
			$this->request->getRequestUri(),
			$this->request->server->all()
		);

		return $this->matched_route;
	}

	/**
	 * Dispatch a request to an action and populate Response
	 *
	 * @param ActionInterface $action
	 * @return Response
	 */
	protected function dispatch(ActionInterface $action)
	{
		// Run the response through the action.
		$action->before($this->request);
		$response = $action($this->request);
		return $action->after($this->request, $response);
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
	 * @return ActionInterface|null
	 * @throws ActionNotFoundException
	 * @throws PageNotFoundException
	 */
	protected function getAction(Request $request)
	{
		// Get the matched route.
		$route = $this->matched_route ?: $this->matchRoute();
		if (!$route) {
			$this->errorRouteNotMatched($request);
		}

		$params = $route->params;

		// Get the appropriate action.
		$action = $this->action_factory->newInstance($params['action'], $params);
		if (!$action) {
			$this->errorActionNotFound($params['action']);
		}

		return $action;
	}

	/**
	 * Logs the requested action and throws an ActionNotFoundException. This is
	 * placed in a protected method so that children can override this behavior
	 * as needed.
	 *
	 * @param string $action Action name
	 * @throws Exceptions\ActionNotFoundException
	 */
	protected function errorActionNotFound($action)
	{
		throw new ActionNotFoundException($action);
	}

	/**
	 * Returns a string to be sent to the browser in the event everything falls
	 * to pieces. This is implemented in a protected method so that children
	 * can override this behavior as needed.
	 *
	 * @return string
	 */
	protected function errorHtmlResponse()
	{
		return '<html><head><title>Oops! Something went wrong.</title></head><body>Oops! Looks like something went wrong.</body></html>';
	}

	/**
	 * Logs the requested page and throws a PageNotFoundException. This is
	 * placed in a protected method so that children can override this behavior
	 * as needed.
	 *
	 * @param Request $request
	 * @throws Exceptions\PageNotFoundException
	 */
	protected function errorRouteNotMatched(Request $request)
	{
		throw new PageNotFoundException;
	}
}
