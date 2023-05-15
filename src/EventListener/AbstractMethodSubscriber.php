<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ProxyableMethodEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractMethodSubscriber implements EventSubscriberInterface
{
    abstract protected function supports(ProxyableMethodEventInterface $event): bool;

    abstract protected function beforeMethod(ProxyableMethodEventInterface $event): void;

    /**
     * @return string[]
     */
    abstract protected static function eventNames(): iterable;

    public function handleEvent(ProxyableMethodEventInterface $event): void
    {
        if ($this->supports($event)) {
            $this->beforeMethod($event);
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
