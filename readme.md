# ATC, An Action-Based PHP Dispatching Library

ATC is a small dispatching library for PHP built on [Aura.Router](https://github.com/auraphp/Aura.Router) package and Symfony's [HttpFoundation](https://github.com/symfony/HttpFoundation) and [EventDispatcher](https://github.com/symfony/EventDispatcher). There are two things you should know about this library:

1. Every single route matches to a single Action class.
2. Exceptions thrown from the Action can implement the ActionInterface and become the new Action.

**"What's an action?"** You can think of an Action as a Controller with a single method. Rather than being responsible for many different routes/pages it is only responsible for a single route. 

## Usage
The simplest possible way to use ATC is by just setting up a route and a corresponding action. Lets do a simple hello world for our home page with an Action called Index.

```php
$router->addGet('Index', '/');
```

```php
namespace Your\Namespace\Prefix;

class Index extends \Aol\Atc\Action
{
    public function __invoke(Request $request)
    {
        return new Response::create('Hello world');
    }
}
```

Now any requests for the home page `/` will match the `Index` Action and send a "Hello world" back to the browser. Remember, this is just using Aura.Router so its pretty easy to build complex paths with named parameters.

```php
$router->addGet('Index', '/{name}/');
```

```php
class Index extends \Aol\Atc\Action
{
    public function __invoke(Request $request)
    {
        return new Response::create('Hello ' . $this->params['name']);
    }
}
```

### Leveraging the presenter
While the method above would be great for simple API responses often you need to be able to do more complex things with templating libraries. ATC will always evaluate the return value of your Action and if it is a Symfony response object it will just send it straight to the browser. If it is not a response object it will take that data and hand it off to the Presenter to handle formatting.

There is an interface for the Presenter class - which makes it very easy to drop in your templating package of choice - but out of the box ATC will handle JSON responses and basic PHP templates. By default it will render the HTML template, but if the request header for the content type is set to `application/json` it will send the json encoded version of your action response. You can always lock an Action down to a single response type by setting the `$allowed_formats` property.

```php
class Index extends \Aol\Atc\Action
{
    protected $allowed_formats = ['text/html'];
    protected $view = 'index';

    public function __invoke(Request $request)
    {
        return ['name' => $this->params['name']];
    }
}
```

```php
<!-- file: your/view/dir/index.php -->
Hello <?=$data['name']?>
```

### Exceptions can be Actions too

Any exception thrown from an ATC Action can immediately replace the current Action as long as implements the `ActionInterface`. Yep, you read that correctly, exceptions can be actions too. For example, if you throw the `NotAuthorizedException` from your Action the dispatcher will verify the exception implements the `ActionInterface` and then redispatch the request using the exception action. In this case it will respond with a `401` HTTP response code and look for a `errors/401` template to use in the presenter.

```php
class Index extends \Aol\Atc\Action
{
    public function __invoke(Request $request)
    {
        throw new \Aol\Atc\Exceptions\NotAuthorizedException;
    }
}
```

You could also create custom exceptions within your own application. For example you could have a `NotSignedInException` that returns a `RedirectResponse` to the signin page:


```php
class NotSignedInException extends \Aol\Atc\Exception
{
    public function __invoke(Request $request)
    {
        return RedirectResponse('/signin/');
    }
}
```

The flexibility is unparalleled and the possibilities are endless.

## Setup
The dispatch class itself can be instantiated with just a few dependencies.

```php
$router = new \Aura\Router\Router;
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$action_factory = new \Aol\Atc\ActionFactory('Your\\Namespace\\Prefix');
$presenter = new \Aol\Atc\Presenter(__DIR__ . '/your/view/dir/';
$event_dispatcher = new \Aol\Atc\EventDispatcher;
$exception_handler = new \Aol\Atc\EventHandlers\DispatchErrorHandler;

$dispatch = new \Aol\Atc\Dispatch(
    $router,
    $request,
    $action_factory,
    $presenter,
    $event_dispatcher,
    $exception_handler
);

$response = $dispatch->run(); // Returns a symfony response object
$response->send();
```

## Installing via Composer
ATC supports PHP 5.4 or above. The recommended way to install ATC is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Guzzle:

```bash
composer require aol/atc
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## FAQ
**WTF is "ATC"?** It originally stood for "Air Traffic Control" but that's a lot of typing and doesn't roll off the tongue very well. 

**How do I inject dependencies into my Action classes?** The included ActionFactory is just the bare bones, but you can inject your own factory into the Dispatcher as long as it implements the `ActionFactoryInterface`.

**What about Twig/Smarty/Blade/etc?** Like the ActionFactory you can inject your own presenter class into the dispatcher as well as long as it implements the `PresenterInterface`. There are a lot of possibilities here so if you do something cool let us know!

**I think I found a bug, now what?** Please, open up an issue! Make sure you tell us what you expected, what happened instead, and include just enough code so that we can reproduce the issue.

**Will you add feature X?** Maybe, but you'll never know until you ask. Open up an issue and we'll discuss it and if you're interested in submitting a pull request check out the Contributing section below.

## Contributing
ATC is an open source project and pull requests are welcome if you'd like to contribute. Please include full unit test coverage and any relevant documentation updates with your PR.

## License
ATC is licensed under the MIT License - see the LICENSE file for details
