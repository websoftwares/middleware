#Middleware (v0.0.1)
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

$throttleMiddleware = new ThrotteObject

$middleware->addHandler($throttleMiddleware);
...
// Add more middleware
...

$middleware = $this->middleware;

// Call
$middleware($request, $response);

```

## Changelog
- v0.0.1: Initial 

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
