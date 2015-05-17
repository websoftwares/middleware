<?php

namespace Websoftwares\Middleware\Adapter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Acquia\Hmac\RequestAuthenticator;
use Acquia\Hmac\Request\RequestInterface as AcquiaRequestInterface;
use Acquia\Hmac\KeyLoaderInterface as AcquiaKeyLoaderInterface;

/**
 * RequestAuthenticatorAdapter.
 *
 * @author Boris <boris@websoftwar.es>
 */
class RequestAuthenticatorAdapter extends AbstractMiddlewareAdapter implements AcquiaRequestInterface
{
    protected $requestAuthenticator;
    protected $keyLoader;
    protected $request;

    public function __construct(
        RequestAuthenticator $requestAuthenticator,
        AcquiaKeyLoaderInterface $keyLoader
        ) {
        $this->keyLoader = $keyLoader;
        $this->requestAuthenticator = $requestAuthenticator;
    }

    /**
     * __invoke.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;

        try {
            // Only use the request for authenitcation no modification
            $this->requestAuthenticator->authenticate($this, $this->keyLoader);
        } catch (\Exception $e) {
            // Re-throw for now.
            throw $e;
        }
    }

    public function hasHeader($header)
    {
        return $this->request->hasHeader($header);
    }

    public function getHeader($header)
    {
        return (string) $this->request->getHeader($header)[0];
    }

    public function getMethod()
    {
        return $this->request->getMethod();
    }

    public function getBody()
    {
        return $this->request->getBody();
    }

    public function getResource()
    {
        return $this->request->getUri();
    }
}
