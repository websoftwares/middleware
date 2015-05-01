<?php

namespace Websoftwares\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * interface MiddlewareInterface.
 *
 * @author Boris <boris@websoftwar.es>
 */
interface MiddlewareInterface
{
    /**
     * __invoke.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response);
}
