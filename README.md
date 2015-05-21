#Middleware (v0.0.*)
This package lets u manage middleware for a HTTP request and response that implement the [PSR-7](https://github.com/php-fig/fig-standards/blob/master/proposed/http-message.md) HTTP message interfaces
`Psr\Http\Message\ServerRequestInterface` and `Psr\Http\Message\ResponseInterface`.

[![Build Status](https://api.travis-ci.org/websoftwares/middleware.png)](https://travis-ci.org/websoftwares/middleware)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/websoftwares/middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/websoftwares/middleware/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/websoftwares/middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/websoftwares/middleware/?branch=master)

## Installing via Composer (recommended)

Install composer in your project:
```
curl -s http://getcomposer.org/installer | php
```

Create a composer.json file in your project root:
```php
{
    "require": {
		"websoftwares/middleware": ~0.0.1"
    }
}
```

Install via composer
```
php composer.phar install
```

## Usage
Basic usage of the `MiddlewareRunner` class.

```php
use Websoftwares\Middleware\MiddlewareRunner;

$middleware = new MiddlewareRunner;

// Some middleware object that is callable through invoke or a closure 
// for consistency u could implement the `Websoftwares\MiddlewareInterface`.

// Invokable object
$throttleMiddleware = new ThrotteObject

// request + middelewareOne decoration <= objects are passed by reference
$middelewareOne = function($request, $response) {
    // Decorate the foo property
    $request->foo = $request->foo + 1;
};

// response + middlewareTwo decoration <= objects are passed by reference
$middlewareTwo = function($request, $response) {
    // / Decorate the bar property
    $response->bar = $response->bar . ' World';
};

$middleware->add($throttleMiddleware);
$middleware->add($middelewareOne);
$middleware->add($middlewareTwo);
...
// Add more middleware
...

$m = $middleware;

// Call
$m($request, $response);

```

## Routing example with external package
Their are many excellent PHP router packages and in time some will be made compatible with PSR-7.
In this basic example we will show u how to use the `MiddlewareRunner` class in conjunction with the latest development version of the [Aura Router package](https://github.com/auraphp/Aura.Router/tree/3.x).


```php
use Websoftwares\Middleware\MiddlewareRunner;
use Aura\Router\RouterContainer;

$routerContainer = new RouterContainer;
$map = $routerContainer->getMap();
$matcher = $routerContainer->getMatcher();

$middleware = new MiddlewareRunner;

// response + middlewareOne decoration <= objects are passed by reference
$middlewareOne = function ($request, $response) {
    // / Decorate the bar property
    $response->bar = $response->bar.' World';
};

$routeIndexAction = function($request, $response) {
    // Awesome sauce
    return $response;
};

// Add middleware
$middleware->add($middlewareTwo);

...
// Add more middleware
...

// Add route as last one
$middleware->add($routeIndexAction);

$map->get('index.read', '/',$middleware); // <-- middleware becomes the handler

// We have a matching route
$route = $matcher->match($request);
$handler = $route->handler;

// Call
$handler($request, $response);

```

## Adapters
At the time of writing PSR-7 is ~~almost on the horizon~~ released :-) and their are many well written community supported HTTP orientated packages but most packages are not yet compliant.

To avoid mass rewrites of all these great packages or waiting for the author and or community to update them or holding out on the advantage of new compliant packages we can make use of the Adapter pattern to make them for example suitable for PSR-7 middleware.

## Adapter RequestAuthenticatorAdapter example
The package [acquia/http-hmac-php](https://github.com/acquia/http-hmac-php) is an implementation of the HTTP HMAC Spec in PHP 
We want to validate the signature throw an exception or continue the middleware stack if it is a valid signature.

```php
use Websoftwares\Middleware\MiddlewareRunner;
use Acquia\Hmac\RequestAuthenticator;


$middleware = new MiddlewareRunner;

// response + middlewareOne decoration <= objects are passed by reference
$middlewareOne = function ($request, $response) {
    // / Decorate the bar property
    $response->bar = $response->bar.' World';
};

// Add middleware
$middleware->add($middlewareOne);

...
// Add more middleware
...

// $keyLoader implements \Acquia\Hmac\KeyLoaderInterface
$authenticator = new RequestAuthenticator(new RequestSigner(), '+15 minutes');

$authenticatorMiddleware = new RequestAuthenticatorAdapter($authenticator, $keyLoader);

$middleware->add($authenticatorMiddleware);

// Call
$m = $middleware;

$m($request, $response);

```

## Changelog
- v0.0.11: Updated psr-7 psr/http-message to 1.0 and renamed phly/http with zendframework/zend-diactoros
- v0.0.10: Logic to exit on response added
- v0.0.9: Added abstract adapter and first implementation "acquia/http-hmac-php" package

## Testing
In the tests folder u can find several tests.

## Acknowledgement
Inspired by all the great middleware packages

- [Interpose](https://github.com/carbocation/interpose)
- [StackPHP](http://stackphp.com)
- [Conduit](https://github.com/bigeasy/conduit)
- [Conduit-php](https://github.com/phly/conduit)
- [Rack](https://github.com/rack/rack)

## License
The [MIT](http://opensource.org/licenses/MIT "MIT") License (MIT).
