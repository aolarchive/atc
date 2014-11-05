<?php

namespace Aol\Atc;

use Aol\Atc\Exceptions\ActionNotFoundException;
use Aol\Atc\Exceptions\ExitDispatchException;
use Aol\Atc\Exceptions\PageNotFoundException;
use Aura\Accept\AcceptFactory;
use Aura\Router\Router;
use Aura\Web\Request;
use Aura\Web\Response;
use Aura\Web\WebFactory;
use Psr\Log\LoggerInterface;

class Dispatch
{
	/** @var \Aol\Atc\ActionFactoryInterface */
	private $action_factory;

	/** @var \Psr\Log\LoggerInterface */
	private $logger;

	/** @var \Aol\Atc\PresenterInterface */
	private $presenter;

	/** @var \Aura\Router\Router */
	private $router;

	/** @var \Aura\Web\WebFactory */
	private $web_factory;

	/** @var bool */
	private $debug_enabled = false;

	/** @var \Aura\Web\Request */
	private $request;

	/** @var \Aura\Web\Response */
	private $response;

	/** @var \Aura\Router\Route */
	private $matched_route;

	/**
	 * @param Router $router
	 * @param WebFactory $web_factory
	 * @param ActionFactoryInterface $action_factory
	 * @param PresenterInterface $presenter
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		Router $router,
		WebFactory $web_factory,
		ActionFactoryInterface $action_factory,
		PresenterInterface $presenter,
		LoggerInterface $logger
	) {
		$this->router         = $router;
		$this->web_factory    = $web_factory;
		$this->action_factory = $action_factory;
		$this->presenter      = $presenter;
		$this->logger         = $logger;
	}

	/**
	 * Returns an instance of Request, creating one if it does not exist
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		if (!isset($this->request)) {
			$this->request = $this->web_factory->newRequest();
		}
		return $this->request;
	}

	/**
	 * Returns an instance of Response, creating one if it does not exist
	 *
	 * @return Response
	 */
	public function getResponse()
	{
		if (!isset($this->response)) {
			$this->response = $this->web_factory->newResponse();
		}
		return $this->response;
	}

	/**
	 * Searches for the target route on the router, returning it or false if it does not exist.
	 *
	 * @return \Aura\Router\Route|false
	 * @throws \Aura\Web\Exception\InvalidComponent
	 */
	protected function matchRoute()
	{
		$request = $this->getRequest();
		$this->matched_route = $this->router->match($request->url->get(PHP_URL_PATH), $request->server->get());
		return $this->matched_route;
	}

	/**
	 * Return true or false if the target route is defined in the router
	 *
	 * @return bool
	 * @throws \Aura\Web\Exception\InvalidComponent
	 */
	public function routeExists()
	{
		if (!isset($this->matched_route)) {
			$this->matched_route = $this->matchRoute();
		}
		return (bool) $this->matched_route;
	}

	/**
	 * Dispatch a request to an action and populate Response
	 *
	 * @param ActionInterface $action
	 * @param Response $response
	 */
	protected function dispatch(ActionInterface $action, Response $response)
	{
		// Run the response through the action.
		$action->before();
		$action($response);
		$action->after();
	}

	/**
	 * Dispatch a response to the presenter
	 *
	 * @param ActionInterface $action
	 * @param Response $response
	 * @throws Exception
	 */
	protected function present(ActionInterface $action, Response $response)
	{
		// Make sure this is not a redirect and then run the response through the presenter.
		if ($response->status->getCode() >= 300 && $response->status->getCode() < 400) {
			return;
		}

		$format  = $this->getMedia($action)->getValue();
		$content = $this->presenter->run($action->getData($format), $format, $action->getView());
		$response->content->set($content);
		$response->content->setType($format);
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
		$action = $this->action_factory->newInstance($params['action'], $request, $params);
		if (!$action) {
			$this->errorActionNotFound($params['action']);
		}

		return $action;
	}

	/**
	 * Dispatch to an action and handle errors
	 *
	 * @throws ExitDispatchException
	 */
	public function run()
	{
		$request  = $this->getRequest();
		$response = $this->getResponse();

		// --------------- Dispatch
		try{
			$action = $this->getAction($request);
			$this->dispatch($action, $response);
		} catch (\Exception $e) {
			$this->debug('Caught 500: ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());

			// This is your escape hatch.
			if ($e instanceof ExitDispatchException) {
				throw $e;
			}

			// Exceptions can implement the action interface to handle themselves. For anything else, use the default
			$action = ($e instanceof ActionInterface) ? $e : new Exception();
			$this->dispatch($action, $response);
		}

		// --------------- Present
		try {
			$this->present($action, $response);
		} catch (\Exception $e) {
			$this->debug('Presentation Exception - ' . get_class($e) . ': ' . $e->getMessage());
			$response->status->set(500);
			$response->content->setType('text/html');
			$response->content->set($this->errorHtmlResponse());
		}

		// --------------- Return
		$this->send($response);
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
	 * Logs the requested action and throws an ActionNotFoundException. This is
	 * placed in a protected method so that children can override this behavior
	 * as needed.
	 *
	 * @param string $action Action name
	 * @throws Exceptions\ActionNotFoundException
	 */
	protected function errorActionNotFound($action)
	{
		$this->debug('Action not found: ' . $action);
		throw new ActionNotFoundException;
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
		$this->debug('No matching route: ' . $request->url->get());
		throw new PageNotFoundException;
	}

	/**
	 * Sends a debug message to the logger.
	 *
	 * @param string $message Debug message
	 */
	private function debug($message)
	{
		if ($this->debug_enabled == true) {
			$this->logger->debug($message);
		}
	}

	/**
	 * Sends the response to the browser.
	 *
	 * @see https://github.com/auraphp/Aura.Web/blob/develop-2/README-RESPONSE.md#sending-the-response
	 *
	 * @param Response $response
	 */
	private function send(Response $response)
	{
		// send status line
		header($response->status->get(), true, $response->status->getCode());

		// send non-cookie headers
		foreach ($response->headers->get() as $label => $value) {
			header("{$label}: {$value}");
		}

		// send cookies
		foreach ($response->cookies->get() as $name => $cookie) {
			setcookie(
				$name,
				$cookie['value'],
				$cookie['expire'],
				$cookie['path'],
				$cookie['domain'],
				$cookie['secure'],
				$cookie['httponly']
			);
		}

		// send content
		echo $response->content->get();
	}
}
