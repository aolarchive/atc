<?php

namespace Aol\Atc\Tests;


use Aol\Atc\Exception;
use Symfony\Component\HttpFoundation\Request;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
	/** @var Exception */
	public $exc;

	public function testExceptionPopulatesActionData()
	{
		$message = 'This is a test message';
		$exc = new Exception($message);
		$exc(new Request());
		$data = $exc->getData();
		$this->assertEquals($message, $data['message']);
	}

	public function testGetAllowedFormatsReturnsArrayOfStrings()
	{
		$exc = new Exception('foo');
		$this->assertContainsOnly('string', $exc->getAllowedFormats());
	}

	public function testGetView()
	{
		$this->assertEquals('errors/500', $this->exc->getView());
	}

	public function testGetHttpCode()
	{
		$this->assertEquals(500, $this->exc->getHttpCode());
	}

	public function setUp()
	{
		$this->exc = new Exception('foo');
	}
}

 