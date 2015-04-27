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
