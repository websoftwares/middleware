<?php

namespace Websoftwares\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware.
 *
 * @author Boris <boris@websoftwar.es>
 */
class Middleware
{
    /**
     * $handlers.
     *
     * @var SplQueue
     */
    protected $handlers;

    /**
     * __construct.
     */
    public function __construct()
    {
        $this->handlers = new \SplQueue();
        $this->handlers->setIteratorMode(
            \SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_KEEP
        );
    }

    /**
     * addHandler.
     *
     * @param callable $handler
     *
     * @return self
     */
    public function addHandler($handler = null)
    {
        // Check if handler is callablde
        if (!is_callable($handler)) {
            throw new \InvalidArgumentException('The handler must be of type callable');
        }

        // Add to Queue
        $this->handlers->push($handler);

        return $this;
    }

    /**
     * __invoke.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response)
    {
        // No handlers found
        if ($this->handlers->count() < 1) {
            return;
        }

        // Default
        $finalHandler = null;

        // Loop over all handlers
        foreach ($this->handlers as $handler) {
            // Next from queue FIFO
            $next = $handler;

            // Call the handler
            $finalHandler = $next($request, $response);
        }

        // Return last from stack
        return $finalHandler;
    }
}
