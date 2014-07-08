<?php

namespace Aol\Atc\Exceptions;

use Aol\Atc\Exception;

class PageNotFoundException extends Exception
{
	protected $http_code = 404;
	protected $view = 'errors/404';
}
