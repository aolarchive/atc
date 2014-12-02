<?php

namespace Aol\Atc\Tests;


use Aol\Atc\ActionFactory;
use Aol\Atc\Dispatch;
use Aol\Atc\Exceptions\PageNotFoundException;
use Aol\Atc\PresenterInterface;
use Aol\Atc\Tests\ActionTest\Action;
use Aura\Router\Regex;
use Aura\Router\Route;
use Aura\Router\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;

class DispatchTest extends \PHPUnit_Framework_TestCase
{

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

	/**
	 * @param $match
	 * @throws \Aol\Atc\Exceptions\ExitDispatchException
	 * @dataProvider runProvider
	 */
	public function testRun($expected, $statuscode, $match, $action, $response, $formats)
	{
		$this->router->shouldReceive('match')->once()->withAnyArgs()->andReturn($match);
		$this->request->shouldIgnoreMissing();
		$this->request->server = $this->getBag('ServerBag');
		$this->request->server->shouldIgnoreMissing([]);
		$this->presenter->shouldReceive('getAvailableFormats')->once()->withNoArgs()->andReturn($formats);
		if (is_null($response)) {
			$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andThrow(new \Exception('foo'));
		} else {
			$this->presenter->shouldReceive('run')->once()->withAnyArgs()->andReturn(new Response());
		}
		$this->action_factory->shouldReceive('newInstance')->once()->withAnyArgs()->andReturn($action);

		if ($match instanceof Route) {
			$c = new \ReflectionClass($match);
			$p = $c->getProperty('params');
			$p->setAccessible(true);
			$p->setValue($match, ['action' => 'foo']);
		}

		/** @var Response $response */
		$response = $this->dispatch->run();

		if ($match === false) {
			$this->assertEquals($statuscode, $response->getStatusCode());
			return;
		}

		if ($action === false) {
			$this->assertEquals($statuscode, $response->getStatusCode());
			return;
		}

		$this->assertEquals($statuscode, $response->getStatusCode());
	}

	public function runProvider()
	{
		return [
			[404, 404, false, new Action([]), new Response(), ['text/html']],
			['/foo/bar', 200, new Route(new Regex(), '/', 'test-route'), new Action([]), new Response(), ['text/html']],
			[500, 500, new Route(new Regex(), '/', 'test-route'), new Action([]), new Response(), []],
			[500, 500, new Route(new Regex(), '/', 'test-route'), false, null, ['text/html']]
		];
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
		$this->dispatch = new Dispatch($this->router, $this->request, $this->action_factory, $this->presenter);
	}

	private function getBag($bag)
	{
		return \Mockery::mock('Symfony\\Component\\HttpFoundation\\' . $bag);
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