<?php

namespace Aol\Atc\Tests;

use Aol\Atc\Presenter;

class PresenterTest extends \PHPUnit_Framework_TestCase
{
	/** @var \Aol\Atc\Presenter */
	private $presenter;

	public function testJsonResponse()
	{
		$response = $this->presenter->run(['foo' => 'bar'], 'application/json', 'test');

		$this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\JsonResponse', $response);
		$this->assertEquals($response->getContent(), '{"foo":"bar"}');
	}

	public function testHtmlResponse()
	{
		$response = $this->presenter->run(['name' => 'Tester'], 'text/html', 'test');

		$this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
		$this->assertEquals($response->getContent(), 'Sup, Tester?' . PHP_EOL);
	}

	protected function setUp()
	{
		$this->presenter = new Presenter(__DIR__ . '/PresenterTest/');
	}
}
