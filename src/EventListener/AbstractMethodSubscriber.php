<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractMethodSubscriber implements EventSubscriberInterface
{
    abstract protected function supports(ManifestMethodEvent $event): bool;
    abstract protected function beforeMethod(ManifestMethodEvent $event): void;

    public function handleEvent(ManifestMethodEvent $event)
    {
        if ($this->supports($event)) {
            $this->beforeMethod($event);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManifestMethodEvent::NAME => ['handleEvent', static::priority()],
        ];
    }

    protected static function priority(): int
    {
        return 0;
    }
}
