<?php

namespace Aol\Atc\Exceptions;

/**
 * This is the only exception that Dispatch::run will not catch. Use it as an
 * escape hatch if you need to exit all the way out of the dispatcher.
 */
class ExitDispatchException extends \Exception
{
}
