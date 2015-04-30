<?php

namespace Websoftwares\Test\Middleware;

use Websoftwares\Middleware\Middleware;
use Phly\Http\ServerRequestFactory;

/**
 * Class MiddlewareTest.
 */
class MiddlewareTest extends \PHPUnit_Framework_TestCase
{
    protected $middleware;
    protected $middleware1;
    protected $middleware2;
    protected $request;
    protected $response;

    public function setUp()
    {
        $this->middleware = new Middleware();
        $this->middleware1 = $this->getMock('Websoftwares\Middleware\HandlerInterface');
        $this->middleware2 = $this->getMock('Websoftwares\Middleware\HandlerInterface');

        $this->request = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $this->response = $this->getMock('Psr\Http\Message\ResponseInterface');
    }

    public function testInstantiateAsObjectSucceeds()
    {
        $this->assertInstanceOf(
            'Websoftwares\Middleware\Middleware',
            $this->middleware);
    }

    public function testAddHandlerSucceeds()
    {
        $actual = $this->middleware->addHandler($this->middleware1);
        $expected = 'Websoftwares\Middleware\Middleware';
        $this->assertInstanceOf($expected, $actual);
    }

    public function testMiddlewareSucceeds()
    {
        $returnValues = array();

        $this->middleware1->expects($this->once())->method('__invoke')
            ->with($this->equalTo($this->request), $this->equalTo($this->response))
            ->will($this->returnCallback(function () use (&$returnValues) {
                $returnValues[] = 1;
            }));

        $this->middleware->addHandler($this->middleware1);

        $this->middleware2->expects($this->once())->method('__invoke')
            ->with($this->equalTo($this->request), $this->equalTo($this->response))
            ->will($this->returnCallback(function () use (&$returnValues) {
                $returnValues[] = 2;
            }));

        $this->middleware->addHandler($this->middleware2);
        $this->middleware->addHandler(function ($request, $response) use (&$returnValues) {
            $returnValues[] = 3;
        });

        $middleware = $this->middleware;

        $actual = $middleware($this->request, $this->response);
        $this->assertNull($actual);

        $expected = array(1,2,3);
        $this->assertEquals($expected, $returnValues);
    }

    public function testPassByReferenceDecorator()
    {
        $request = $this->request;
        $request->foo = 1;
        $response = $this->response;
        $response->bar = 'Hello';

        $expectedRequest = 6;
        $epectedResponse = 'Hello World';

        // request + middelewareOne decoration <= objects are passed by reference
        $middelewareOne = function ($request, $response) {
            // Decorate the foo property
            $request->foo = $request->foo + 1;
        };

        // response, RequestMiddelewareOne +  middlewareTwo decorations <= objects are passed by reference
        $middlewareTwo = function ($request, $response) {
            // / Decorate the bar property
            $response->bar = $response->bar.' World';
            $request->foo = $request->foo + 4;
        };

        $this->middleware->addHandler($middelewareOne);
        $this->middleware->addHandler($middlewareTwo);

        $middleware = $this->middleware;

        $r = $middleware($request, $response);

        $this->assertEquals($expectedRequest, $request->foo);
        $this->assertEquals($epectedResponse, $response->bar);
    }

    protected function newRequest($path, array $server = [])
    {
        $server['REQUEST_URI'] = $path;
        $server = array_merge($_SERVER, $server);

        return ServerRequestFactory::fromGlobals($server);
    }

    public function testWithPr7RouterPackageAuraV3()
    {
        $routerContainer = new \Aura\Router\RouterContainer();
        $map = $routerContainer->getMap();
        $matcher = $routerContainer->getMatcher();

        $request = $this->newRequest('/');
        $response = $this->response;

        $response->bar = 'Hello';
        $expectedResponse = 'Hello World';

        // response + middlewareOne decoration <= objects are passed by reference
        $middlewareOne = function ($request, $response) {
            // / Decorate the bar property
            $response->bar = $response->bar.' World';
        };

        $routeIndexAction = function ($request, $response) {
            // Awesome sauce
            return $response->bar;
        };

        // Add middleware
        $this->middleware->addHandler($middlewareOne);

        // Add route as last one
        $this->middleware->addHandler($routeIndexAction);

        $map->get('index.read', '/', $this->middleware); // <-- middleware becomes the handler

        // We have a matching route
        $route = $matcher->match($request);
        $h = $route->handler;
        $responseResult = $h($request, $response);
        $this->assertEquals($expectedResponse, $responseResult);
    }

    /**
     * testAddHandlerFailsOnException.
     *
     * @expectedException Exception
     */
    public function testMiddlewareFailsException()
    {
        $exception = new \Exception('test', 1);

        $this->middleware1->expects($this->once())->method('__invoke')
            ->with($this->equalTo($this->request), $this->equalTo($this->response))
            ->will($this->throwException($exception));

        $this->middleware->addHandler($this->middleware1);

        $middleware = $this->middleware;

        $actual = $middleware($this->request, $this->response);
    }

    /**
     * testAddHandlerFailsOnException.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddHandlerFailsOnException()
    {
        $this->middleware->addHandler();
    }

    public function testMiddlewareFailsOnNoHandlers()
    {
        $middleware = new Middleware();
        $app = $middleware($this->request, $this->response);

        $this->assertNull($app);
    }
}
