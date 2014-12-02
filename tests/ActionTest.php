<?php

namespace Aol\Atc\Tests;

use Aol\Atc\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestAction extends Action
{

	/**
	 * Takes a response object and returns an array of data. Formatting will be
	 * handled by a Presenter.
	 *
	 * @param Request $request
	 * @return Response|void
	 */
	public function __invoke(Request $request)
	{
		$this->data['content'] = $request->getContent();
	}
}

class ActionTest extends \PHPUnit_Framework_TestCase
{
	/** @var Request */
	public $request;

	/** @var Action */
	public $action;

	public function testGetView()
	{
		$this->assertEquals('', $this->action->getView());
	}

	public function testGetHttpCode()
	{
		$this->assertEquals(200, $this->action->getHttpCode());
	}

	public function testGetAllowedFormatsReturnsArrayOfStrings()
	{
		$this->assertContainsOnly('string', $this->action->getAllowedFormats());
	}

	public function testGetData()
	{
		$this->assertEquals([], $this->action->getData());
	}

	public function setUp()
	{
		$this->request = new Request(['message' => "I'm a chunky monkey from funky town"]);
		$this->action  = new TestAction([]);
	}
}
 