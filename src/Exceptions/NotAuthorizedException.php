<?php

namespace Amp\Atc\Exceptions;

use Aol\Atc\Exception;

class NotAuthorizedException extends Exception
{
	protected $data = ['status' => 'failure', 'message' => 'not authorized'];
	protected $http_code = 401;
	protected $view = 'errors/401';
}
