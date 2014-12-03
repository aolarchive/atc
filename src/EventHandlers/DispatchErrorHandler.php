<?php

namespace Aol\Atc\EventHandlers;

use Aol\Atc\Events\DispatchErrorEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class DispatchErrorHandler implements EventHandlerInterface
{
	/**
	 * @return string
	 */
	protected function getErrorHtml()
	{
		return '<html><head><title>Oops! Something went wrong.</title></head><body>Oops! Looks like something went wrong.</body></html>';
	}

	/**
	 * @param Event $event
	 * @return mixed|Response
	 */
	public function __invoke(Event $event)
	{
		/** @var DispatchErrorEvent $event */
		$exc = $event->getException();
		return new Response(
			$event->isDebug() ? $exc->getMessage() : $this->getErrorHtml(),
			Response::HTTP_INTERNAL_SERVER_ERROR,
			['content-type' => 'text/html']
		);
	}

}