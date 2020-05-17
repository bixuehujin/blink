<?php

declare(strict_types=1);

namespace blink\eventbus;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

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
            $listener($event);
        }

        return $event;
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
