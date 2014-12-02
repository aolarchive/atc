<?php

namespace Aol\AtcExample\Actions;

use Aol\Atc\Action;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Redir extends Action
{
	public function __invoke(Request $request)
	{
		return new StreamedResponse(function() { echo 'fozo'; });
	}
}
