<?php

declare(strict_types=1);

namespace blink\eventbus;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Class EventBus
 *
 * @package blink\eventbus
 */
class EventBus implements EventDispatcherInterface, ListenerProviderInterface
{
    protected array $listeners = [];

    /**
     * Attach a handler to the given eventClass.
     *
     * @param string $eventClass
     * @param callable $handler
     */
    public function attach(string $eventClass, callable $handler)
    {
        $this->listeners[$eventClass][] = $handler;
    }

    /**
     * Dispatch a event.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event)
    {
        $listeners = $this->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
            $listener($event);
        }

        return $event;
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

    public function getListenersForEvent(object $event): iterable
    {
        yield from $this->listenersFor(get_class($event));
        yield from $this->listenersFor(...class_implements($event));
        yield from $this->listenersFor(...class_parents($event));
    }

    protected function listenersFor(string ...$classNames): iterable
    {
        foreach ($classNames as $className) {
            if (isset($this->listeners[$className])) {
                yield from $this->listeners[$className];
            }
        }
    }
}
