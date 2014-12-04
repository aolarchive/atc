<?php

namespace Aol\Atc\EventHandlers;


use Symfony\Component\EventDispatcher\Event;

interface EventHandlerInterface
{
	/**
	 * @param Event $event
	 * @return mixed
	 */
	public function __invoke(Event $event);
} 