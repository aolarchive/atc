<?php

namespace Aol\Atc\Tests;


use Aol\Atc\EventHandlers\DispatchErrorHandler;
use Aol\Atc\Events\DispatchErrorEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DispatchErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
	public function testInvocation()
	{
		$html    = '<html><head><title>Oops! Something went wrong.</title></head><body>Oops! Looks like something went wrong.</body></html>';
		$event   = new DispatchErrorEvent(new \Exception(), new Request([]));
		$handler = new DispatchErrorHandler();
		$response = $handler($event);
		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals($html, $response->getContent());
	}

	public function testDebugInvocation()
	{
		$event = new DispatchErrorEvent(new \Exception('foo'), new Request([]), true);
		$handler = new DispatchErrorHandler();
		$response = $handler($event);
		$this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
		$this->assertEquals('foo', $response->getContent());
	}
}
 