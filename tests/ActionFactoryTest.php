<?php

namespace Aol\Atc\Tests;

use Aol\Atc\Action;
use Aol\Atc\ActionFactory;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
	public function testFactoryCreatesClass()
	{
		$params = ['foo' => 'bar'];
		$factory = new ActionFactory('Aol\\Atc\\Tests\\ActionTest\\');

		/** @var Action $action */
		$action = $factory->newInstance('Action', $params);

		$this->assertInstanceOf('Aol\Atc\ActionInterface', $action);
	}
}
