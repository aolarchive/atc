<?php

namespace Aol\Atc\Tests;


use Aol\Atc\Action\ServerError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ServerErrorTest
 * @package Aol\Atc\Tests
 */
class ServerErrorTest extends \PHPUnit_Framework_TestCase
{
	public function testInvokeSetsStatusCode500()
	{
		$action = new ServerError([]);
		/** @var Response $response */
		$response = $action(new Request([]));
		$this->assertEquals(500, $response->getStatusCode());
	}
}
 