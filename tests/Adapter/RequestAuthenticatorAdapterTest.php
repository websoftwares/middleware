<?php

namespace Websoftwares\Test\Middleware\Adapter;

use Websoftwares\Middleware\Adapter\RequestAuthenticatorAdapter;
use Zend\Diactoros\ServerRequestFactory;
use Acquia\Hmac\RequestAuthenticator;
use Acquia\Hmac\RequestSigner;
use Acquia\Hmac\Test\DummyKeyLoader;
use Websoftwares\Middleware\MiddlewareRunner;

/**
 * Class RequestAuthenticatorAdapterTest.
 */
class RequestAuthenticatorAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $response;
    protected $middleware;
    protected $middleware1;

    public function setUp()
    {
        $this->response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $this->middleware = new MiddlewareRunner();
        $this->middleware1 = $this->getMock('Websoftwares\Middleware\MiddlewareInterface');
    }

    public function testValidSignature()
    {
        $returnValues = array();

        $signer = new RequestSigner();
        $signer->addCustomHeader('Custom1');

        $headers = array(
            'Content-Type' => 'text/plain',
            'Date' => 'Fri, 19 Mar 1982 00:00:04 GMT',
            'Authorization' => 'Acquia 1:'.'WkDE6I+sDSqSKKYnZrHhT3MiHLU=',
            'Custom1' => 'Value1',
        );

        $request = $this->newRequest();

        // Add headers
        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $authenticator = new RequestAuthenticator($signer, 0);
        $keyLoader = new DummyKeyLoader();

        $authenticator = new RequestAuthenticatorAdapter($authenticator, $keyLoader);

        $this->assertNull($authenticator($request, $this->response));

        $this->middleware1->expects($this->once())->method('__invoke')
            ->with($this->equalTo($request), $this->equalTo($this->response))
            ->will($this->returnCallback(function () use (&$returnValues) {
                $returnValues[] = 1;
            }));

        $this->middleware->add($this->middleware1);
        $this->middleware->add($authenticator);

        $middleware = $this->middleware;
        $actual = $middleware($request, $this->response);
        $this->assertNull($actual);

        $expected = array(1);
        $this->assertEquals($expected, $returnValues);
    }

    /**
     * @expectedException \Acquia\Hmac\Exception\InvalidSignatureException
     */
    public function testInvalidSignature()
    {
        $signer = new RequestSigner();
        $signer->addCustomHeader('Custom1');

        $headers = array(
            'Content-Type' => 'text/plain',
            'Date' => 'Fri, 19 Mar 1982 00:00:04 GMT',
            'Authorization' => 'Acquia 1:badsignature',
            'Custom1' => 'Value1',
        );

        $request = $this->newRequest();

        // Add headers
        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $authenticator = new RequestAuthenticator($signer, 0);
        $keyLoader = new DummyKeyLoader();

        $authenticator = new RequestAuthenticatorAdapter($authenticator, $keyLoader);
        $authenticator($request, $this->response);
    }

    /**
     * @expectedException \Acquia\Hmac\Exception\TimestampOutOfRangeException
     */
    public function testExpiredRequest()
    {
        $signer = new RequestSigner();
        $headers = array(
            'Content-Type' => 'text/plain',
            'Date' => 'Fri, 19 Mar 1982 00:00:04 GMT',
            'Authorization' => 'Acquia 1:'.'WkDE6I+sDSqSKKYnZrHhT3MiHLU=',
        );

        $request = $this->newRequest();

        // Add headers
        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $authenticator = new RequestAuthenticator($signer, '10 minutes');
        $keyLoader = new DummyKeyLoader();

        $authenticator = new RequestAuthenticatorAdapter($authenticator, $keyLoader);

        $authenticator($request, $this->response);
    }

    /**
     * @expectedException \Acquia\Hmac\Exception\TimestampOutOfRangeException
     */
    public function testFutureRequest()
    {
        $signer = new RequestSigner();
        $time = new \DateTime('+11 minutes');

        $headers = array(
            'Content-Type' => 'text/plain',
            'Date' => $time->format('D, d M Y H:i:s \G\M\T'),
            'Authorization' => 'Acquia 1:'.'WkDE6I+sDSqSKKYnZrHhT3MiHLU=',
        );

        $request = $this->newRequest();

        // Add headers
        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $authenticator = new RequestAuthenticator($signer, '10 minutes');
        $keyLoader = new DummyKeyLoader();

        $authenticator = new RequestAuthenticatorAdapter($authenticator, $keyLoader);

        $authenticator($request, $this->response);
    }

    protected function newRequest()
    {
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/resource/1?key=value',
            'QUERY_STRING' => 'key=value',
        ];

        $server = array_merge($_SERVER, $server);

        return ServerRequestFactory::fromGlobals($server);
    }
}
