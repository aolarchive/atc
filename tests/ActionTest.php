<?php

namespace Aol\Atc\Tests;

use Aol\Atc\ActionFactory;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
	public function testFactoryCreatesClass()
	{
		$params = ['foo' => 'bar'];
		$factory = new ActionFactory('Aol\\Atc\\Tests\\ActionTest\\');
		$action = $factory->newInstance('Action', $params);

		$this->assertInstanceOf('Aol\Atc\ActionInterface', $action);
		$this->assertEquals($params, $action->getParams());
	}
}
