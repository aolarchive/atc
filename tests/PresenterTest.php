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

	public function testBinaryFileResponse()
	{
		$file = __DIR__ . '/fixtures/image.jpg';
		$response = $this->presenter->run($file, 'image/jpeg');
		$this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\BinaryFileResponse', $response);
		$this->assertFileEquals($file, $response->getFile()->getFileInfo()->getRealPath());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage View does not exist: file-that-doesnt-exist-so-forget-about-it
	 */
	public function testPresenterThrowsExceptionOnInvalidView()
	{
		$this->presenter->run(['foo' => 'bar'], 'text/html', 'file-that-doesnt-exist-so-forget-about-it');
	}

	public function testGetAvailableFormatsReturnsArrayOfStrings()
	{
		$this->assertContainsOnly('string', $this->presenter->getAvailableFormats());
	}

	protected function setUp()
	{
		$this->presenter = new Presenter(__DIR__ . '/PresenterTest/');
	}
}
