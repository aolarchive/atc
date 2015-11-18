<?php

namespace Aol\Atc\Exceptions;

use Aol\Atc\Exception;
use Symfony\Component\HttpFoundation\Request;

class ActionNotFoundException extends Exception
{
	protected $data = ['status' => 'error', 'message' => 'internal error - action not found'];
	protected $http_code = 500;
	protected $view = 'errors/500';
}
