<?php

namespace Aol\Atc\Exceptions;

use Aol\Atc\Exception;
use Symfony\Component\HttpFoundation\Request;

class ActionNotFoundException extends Exception
{
	protected $data = ['status' => 'failure', 'message' => 'action not found'];

	public function __invoke(Request $request)
	{
		$this->data['action'] = $this->getMessage();
	}
}
