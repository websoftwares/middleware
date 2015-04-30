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
Basic usage of the `Middleware` class.

```php
use Websoftwares\Middleware;

$middleware = new Middleware;

// Some middleware object that is callable through invoke or a closure 
// for consistency u could implement the `Websoftwares\HandlerInterface`.

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

$middleware->addHandler($throttleMiddleware);
$middleware->addHandler($middelewareOne);
$middleware->addHandler($middlewareTwo);
...
// Add more middleware
...

$m = $middleware;

// Call
$m($request, $response);

```

## Routing example with external package
Their are many excellent PHP router packages and in time some will be made compatible with PSR-7.
In this basic example we will show u how to use the `Middleware` class in conjunction with the latest development version of the [Aura Router package](https://github.com/auraphp/Aura.Router/tree/3.x).


```<?php  ?>
use Websoftwares\Middleware;
use Aura\Router\RouterContainer;

$routerContainer = new RouterContainer;
$map = $routerContainer->getMap();
$matcher = $routerContainer->getMatcher();

$middleware = new Middleware;

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
$middleware->addHandler($middlewareTwo);

...
// Add more middleware
...

// Add route as last one
$middleware->addHandler($routeIndexAction);

$map->get('index.read', '/',$middleware); // <-- middleware becomes the handler

// We have a matching route
$route = $matcher->match($request);
$handler = $route->handler;

// Call
$handler($request, $response);

```

## Changelog
- v0.0.5: fFxes from scrutinizer suggestions.
- v0.0.4: finalHandler + external package example.
- v0.0.3: Decorate the request and response example and tests added.
- v0.0.2: Small fixes.
- v0.0.1: Initial.

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
