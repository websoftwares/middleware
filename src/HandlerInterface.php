<?php

namespace Websoftwares\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * interface HandlerInterface.
 *
 * @author Boris <boris@websoftwar.es>
 */
interface HandlerInterface
{
    /**
     * __invoke.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response);
}
