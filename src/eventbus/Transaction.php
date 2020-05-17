<?php

declare(strict_types=1);

namespace blink\eventbus;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Transaction
 *
 * @package blink\eventbus
 */
class Transaction implements EventDispatcherInterface
{
    protected EventDispatcherInterface $bus;
    protected array                    $events = [];

    public function __construct(EventDispatcherInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Starts a new transaction.
     *
     * @return Transaction
     */
    public function beginTransaction(): Transaction
    {
        return new Transaction($this);
    }

    /**
     * Dispatch event to the transaction.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event)
    {
        $this->events[] = $event;
        return $event;
    }

    /**
     * Commit the transaction.
     */
    public function commit()
    {
        foreach ($this->events as $event) {
            $this->bus->dispatch($event);
        }
    }

    /**
     * Rollback the transaction.
     */
    public function rollback()
    {
        $this->events = [];
    }
}
