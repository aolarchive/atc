<?php

namespace Aol\Atc;

use Aura\Di\Container;

class Config extends \Aura\Di\Config
{
	public function define(Container $di)
	{
		$di->set('Aol\\Atc\\Dispatch', $di->lazyNew('Aol\\Atc\\Dispatch'));

		$di->set('Aol\\Atc\\ActionFactory', $di->lazyNew('Aol\\Atc\\ActionFactory'));
		$di->set('Aol\\Atc\\ActionFactoryInterface', $di->lazyGet('Aol\\Atc\\ActionFactory'));

		$di->set('Aol\\Atc\\Presenter', $di->lazyNew('Aol\\Atc\\Presenter'));
		$di->set('Aol\\Atc\\PresenterInterface', $di->lazyGet('Aol\\Atc\\Presenter'));

		$di->params['Aol\\Atc\\Dispatch'] = [
			'router'         => $di->lazyGet('Aura\\Router\\Router'),
			'web_factory'    => $di->lazyGet('Aura\\Web\\WebFactory'),
			'action_factory' => $di->lazyGet('Aol\\Atc\\ActionFactoryInterface'),
			'presenter'      => $di->lazyGet('Aol\\Atc\\PresenterInterface'),
			'logger'         => $di->lazyGet('Psr\\Log\\LoggerInterface')
		];
	}
}
