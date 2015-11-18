<?php

namespace Aol\Atc\Exceptions;

use Aol\Atc\Exception;

class DispatchException extends Exception
{
	protected $data = ['status' => 'error', 'message' => 'internal error'];
	protected $http_code = 500;
	protected $view = 'errors/500';
}
