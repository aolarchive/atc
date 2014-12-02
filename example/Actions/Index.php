<?php

namespace Aol\AtcExample\Actions;

use Aol\Atc\Action;
use Aura\Web\Response;
use Symfony\Component\HttpFoundation\Request;

class Index extends Action
{
	protected $view = 'index';

	/**
	 * @inheritdoc
	 */
	public function __invoke(Request $request)
	{
		$this->data = ['name' => 'Ralph'];
	}
}
