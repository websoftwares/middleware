<?php

namespace Websoftwares\Test\Middleware;

use Websoftwares\Middleware\Middleware;

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

        $middleware($request, $response);

        $this->assertEquals($expectedRequest, $request->foo);
        $this->assertEquals($epectedResponse, $response->bar);
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
