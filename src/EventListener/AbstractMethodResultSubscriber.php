<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractMethodResultSubscriber implements EventSubscriberInterface
{
    abstract protected function supports(ManifestMethodCalledEvent $event): bool;
    abstract protected function afterMethod(ManifestMethodCalledEvent $event): void;

    public function handleEvent(ManifestMethodCalledEvent $event)
    {
        if ($this->supports($event)) {
            $this->afterMethod($event);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManifestMethodCalledEvent::NAME => ['handleEvent', static::priority()],
        ];
    }

    protected static function priority(): int
    {
        return 0;
    }
}
