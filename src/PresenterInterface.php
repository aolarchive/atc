<?php

namespace Aol\Atc;

use Aura\Web\Response;

interface PresenterInterface
{
	public function run(Response $response, array $allowed_formats, $view = null);
}
