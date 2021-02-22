<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;

class ResourcesMethodSubscriber extends AbstractEnvAwareMethodSubscriber
{
    protected function methodName(): string
    {
        return 'resources';
    }

    protected function supports(ManifestMethodCalledEvent $event): bool
    {
        return $event->manifest() instanceof ContainerInterface;
    }
}
