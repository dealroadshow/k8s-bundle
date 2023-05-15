<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ProxyableMethodCalledEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractMethodResultSubscriber implements EventSubscriberInterface
{
    abstract protected function supports(ProxyableMethodCalledEventInterface $event): bool;

    abstract protected function afterMethod(ProxyableMethodCalledEventInterface $event): void;

    /**
     * @return string[]
     */
    abstract protected static function eventNames(): iterable;

    public function handleEvent(ProxyableMethodCalledEventInterface $event): void
    {
        if ($this->supports($event)) {
            $this->afterMethod($event);
        }
    }

    public static function getSubscribedEvents(): array
    {
        $events = [];
        foreach (static::eventNames() as $eventName) {
            $events[$eventName] = ['handleEvent', static::priority()];
        }

        return $events;
    }

    protected static function priority(): int
    {
        return 0;
    }
}
