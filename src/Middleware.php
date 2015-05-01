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
     * $queue.
     *
     * @var SplQueue
     */
    protected $queue;

    /**
     * __construct.
     */
    public function __construct()
    {
        $this->queue = new \SplQueue();
        $this->queue->setIteratorMode(
            \SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_KEEP
        );
    }

    /**
     * add.
     *
     * @param callable $callable
     *
     * @return self
     */
    public function add($callable = null)
    {
        // Check if callable is callablde
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Add a valid callable');
        }

        // Add to Queue
        $this->queue->push($callable);

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
        // No callables found
        if ($this->queue->count() < 1) {
            return;
        }

        // Default
        $last = null;

        // Loop over the queue
        foreach ($this->queue as $callable) {
            // Next from queue FIFO
            $next = $callable;

            // Call the callable
            $last = $next($request, $response);
        }

        // Return last from queue
        return $last;
    }
}
