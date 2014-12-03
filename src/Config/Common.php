<?php

namespace Aol\Atc\Config;

use Aura\Di\Container;

class Common extends \Aura\Di\Config
{
	public function define(Container $di)
	{
		$di->set('Aol\\Atc\\Dispatch', $di->lazyNew('Aol\\Atc\\Dispatch'));

		$di->set('Aol\\Atc\\ActionFactory', $di->lazyNew('Aol\\Atc\\ActionFactory'));
		$di->set('Aol\\Atc\\ActionFactoryInterface', $di->lazyGet('Aol\\Atc\\ActionFactory'));

		$di->set('Aol\\Atc\\Presenter', $di->lazyNew('Aol\\Atc\\Presenter'));
		$di->set('Aol\\Atc\\PresenterInterface', $di->lazyGet('Aol\\Atc\\Presenter'));

		$di->set('Aol\\Atc\\EventDispatcher', $di->lazyNew('Aol\\Atc\\EventDispatcher'));
		$di->set('Aol\\Atc\\EventHandlers\\DispatchErrorHandler', $di->lazyNew('Aol\\Atc\\EventHandlers\\DispatchErrorHandler'));

		$di->params['Aol\\Atc\\Dispatch'] = [
			'router'            => $di->lazyGet('Aura\\Router\\Router'),
			'web_factory'       => $di->lazyGet('Aura\\Web\\WebFactory'),
			'action_factory'    => $di->lazyGet('Aol\\Atc\\ActionFactoryInterface'),
			'presenter'         => $di->lazyGet('Aol\\Atc\\PresenterInterface'),
			'event_dispatcher'  => $di->lazyGet('Aol\\Atc\\EventDispatcher'),
			'exception_handler' => $di->lazyGet('Aol\\Atc\\EventHandlers\\DispatchErrorHandler')
		];
	}
}
