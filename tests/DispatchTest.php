<?php

namespace Aol\Atc\Tests;

use Aol\Atc\ActionFactory;
use Aol\Atc\ActionInterface;
use Aol\Atc\Dispatch;
use Aol\Atc\DispatchEvents;
use Aol\Atc\EventDispatcher;
use Aol\Atc\EventHandlers\DispatchErrorHandler;
use Aol\Atc\Exceptions\PageNotFoundException;
use Aol\Atc\PresenterInterface;
use Aol\Atc\Tests\ActionTest\Action;
use Aura\Router\Regex;
use Aura\Router\Route;
use Aura\Router\Router;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;

class ExceptionThrowingDummyAction extends \Aol\Atc\Action {
	public function __invoke(Request $request) {
		throw new \Exception();
	}
}

class DispatchableExceptionThrowingDummyAction extends \Aol\Atc\Action {
	public function __invoke(Request $request) {
		throw new PageNotFoundException();
	}
}

class DispatchTest extends \PHPUnit_Framework_TestCase
{

	const CASE_PAGE_NOT_FOUND = 0;
	const CASE_ACTION_THROWS_EXCEPTION = 1;
	const CASE_ACTION_THROWS_ACTION_EXCEPTION = 2;
	const CASE_PRESENTER_THROWS_EXCEPTION = 3;

	/** @var Dispatch */
	private $dispatch;

	/** @var \Mockery\MockInterface */
	private $router;

	/** @var \Mockery\MockInterface */
	private $request;

	/** @var \Mockery\MockInterface */
	private $action_factory;

	/** @var \Mockery\MockInterface */
	private $presenter;

	/** @var \Mockery\MockInterface */
	private $event_dispatcher;

	private $exception_handler;

	public function testDebugToggles()
	{
		$read_debug_state = function (Dispatch $dispatch) {
			$c = new \ReflectionClass($dispatch);
			$p = $c->getProperty('debug_enabled');
			$p->setAccessible(true);
			$value = $p->getValue($dispatch);
			return $value;
		};

		$this->dispatch->enableDebug();
		$this->assertEquals(true, $read_debug_state($this->dispatch));

		$this->dispatch->disableDebug();
		$this->assertEquals(false, $read_debug_state($this->dispatch));
	}

	/**
	 * @param $expected
	 * @param $match
	 * @dataProvider matchRouteProvider
	 */
	public function testRouting($expected, $match)
	{
		$this->router->shouldReceive('match')->once()->withAnyArgs()->andReturn($match);
		$this->request->shouldIgnoreMissing();
		$this->request->server = $this->getBag('ServerBag');
		$this->request->server->shouldIgnoreMissing([]);
		$this->assertEquals($expected, $this->dispatch->routeExists());
	}

	private function setUpTestRun($case)
	{
		// Matcher
		$this->router->shouldReceive('match')->once()->withAnyArgs()->andReturn($case['route']);

		// Request
		$this->request->shouldIgnoreMissing();
		$this->request->server = $this->getBag('ServerBag');
		$this->request->server->shouldIgnoreMissing([]);

		$this->presenter->shouldReceive('getAvailableFormats')->once()->withNoArgs()->andReturn($case['formats']);

		$this->action_factory->shouldReceive('newInstance')->once()->withAnyArgs()->andReturn($case['action']);

		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::PRE_DISPATCH, \Mockery::any())->andReturnNull();
		$this->event_dispatcher->shouldReceive('dispatch')->with(DispatchEvents::POST_DISPATCH, \Mockery::any())->andReturnNull();
		$this->event_dispatcher->shouldReceive('dispatch')->with(DispatchEvents::PRE_PRESENT, \Mockery::any())->andReturnNull();
		$this->event_dispatcher->shouldReceive('dispatch')->with(DispatchEvents::POST_PRESENT, \Mockery::any())->andReturnNull();

	}

	public function testDispatchWhenPageNotFound()
	{
		$case = [
			'action' 	   => new Action([]),
			'status_code'  => Response::HTTP_NOT_FOUND,
			'route' 	   =>false,
			'response_obj' => new Response(),
			'formats'	   => ['text/html'],
		];
		$this->setUpTestRun($case);
		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn($case['response_obj']);
		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::DISPATCH_ERROR, \Mockery::type('Aol\\Atc\\Events\\DispatchErrorEvent'));

		$response = $this->dispatch->run();
		$this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
		$this->assertEquals($case['status_code'], $response->getStatusCode());
	}

	public function testDispatchWhenActionThrowsException()
	{
		$case = [
			'action'       => new ExceptionThrowingDummyAction([]),
			'status_code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
			'route'        => $this->getMatchedRoute(),
			'response_obj' => new Response(),
			'formats'      => ['text/html'],
		];
		$this->setUpTestRun($case);
		$case['route'] = $this->setUpRoute($case['route']);

		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn($case['response_obj']);

		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::DISPATCH_ERROR, \Mockery::type('Aol\\Atc\\Events\\DispatchErrorEvent'));
		$response = $this->dispatch->run();
		$this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
	}

	public function testDispatchWhenActionThrowsDispatchableException()
	{
		$case = [
			'action'       => new DispatchableExceptionThrowingDummyAction([]),
			'status_code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
			'route'        => $this->getMatchedRoute(),
			'response_obj' => new Response(),
			'formats'      => ['text/html'],
		];
		$this->setUpTestRun($case);
		$case['route'] = $this->setUpRoute($case['route']);

		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn($case['response_obj']);

		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::DISPATCH_ERROR, \Mockery::type('Aol\\Atc\\Events\\DispatchErrorEvent'));
		$response = $this->dispatch->run();
		$this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
	}

	public function testDispatchWhenPresenterThrowsException()
	{
		$case = [
			'action' => new Action([]),
			'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
			'route' => $this->getMatchedRoute(),
			'response_obj' => new \Exception(),
			'formats' => ['text/html']
		];
		$this->setUpTestRun($case);
		$case['route'] = $this->setUpRoute($case['route']);
		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andThrow($case['response_obj']);
		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn(new Response('', 500));

		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::DISPATCH_ERROR, \Mockery::type('Aol\\Atc\\Events\\DispatchErrorEvent'));
		$response = $this->dispatch->run();
		$this->assertEquals($case['status_code'], $response->getStatusCode());
	}

	public function testDispatchWhenMediaNegotiationsFail()
	{
		$case = [
			'action' => new Action([]),
			'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
			'route' => $this->getMatchedRoute(),
			'response_obj' => new Response(),
			'formats' => []
		];
		$this->setUpTestRun($case);
		$case['route'] = $this->setUpRoute($case['route']);
		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn($case['response_obj']);


		$this->setExpectedException('Exception');
		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::DISPATCH_ERROR, \Mockery::type('Aol\\Atc\\Events\\DispatchErrorEvent'));
		$response = $this->dispatch->run();
		$this->assertEquals($case['status_code'], $response->getStatusCode());
	}

	public function testDispatchWhenActionNotFound()
	{
		$case = [
			'action' => null,
			'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
			'route' => $this->getMatchedRoute(),
			'response_obj' => new Response(),
			'formats' => []
		];
		$this->setUpTestRun($case);
		$case['route'] = $this->setUpRoute($case['route']);
		$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn($case['response_obj']);


		$this->setExpectedException('Exception');
		$this->event_dispatcher->shouldReceive('dispatch')->once()->with(DispatchEvents::DISPATCH_ERROR, \Mockery::type('Aol\\Atc\\Events\\DispatchErrorEvent'));
		$response = $this->dispatch->run();
		$this->assertEquals($case['status_code'], $response->getStatusCode());
	}

	private function setUpRoute($route)
	{
		$c = new \ReflectionClass($route);
		$p = $c->getProperty('params');
		$p->setAccessible(true);
		$p->setValue($route, ['action' => 'foo']);
		return $route;
	}

	private function getMatchedRoute()
	{
		return new Route(new Regex(), '/', 'test-route');
	}

	public function matchRouteProvider()
	{
		return [
			[true, new Route(new Regex(), '/', 'test-route')],
			[false, false]
		];
	}

	public function setUp()
	{
		$this->router = \Mockery::mock('Aura\\Router\\Router');
		$this->request = $this->setUpRequest();
		$this->action_factory = $this->setUpActionFactory();
		$this->presenter = $this->setUpPresenter();
		$this->event_dispatcher = $this->setUpEventDispatcher();
		$this->exception_handler = $this->setUpExceptionHandler();
		$this->event_dispatcher->shouldReceive('addListener')->andReturnNull();
		$this->dispatch = new Dispatch($this->router, $this->request, $this->action_factory, $this->presenter, $this->event_dispatcher, $this->exception_handler);
	}

	private function getBag($bag)
	{
		return \Mockery::mock('Symfony\\Component\\HttpFoundation\\' . $bag);
	}

	private function setUpExceptionHandler()
	{
		return new DispatchErrorHandler();
	}

	private function setUpEventDispatcher()
	{
		$dispatcher = \Mockery::Mock('Aol\\Atc\\EventDispatcher');
		return $dispatcher;
	}
	private function setUpRequest()
	{
		$request = \Mockery::mock('Symfony\\Component\\HttpFoundation\\Request');
		return $request;
	}

	private function setUpPresenter()
	{
		$presenter = \Mockery::mock('Aol\\Atc\\PresenterInterface');
		return $presenter;
	}

	private function setUpActionFactory()
	{
		$factory = \Mockery::mock('Aol\\Atc\\ActionFactoryInterface');
		return $factory;
	}
}
