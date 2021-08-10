<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Persistence\PersistentVolumeClaimInterface;

class ResourcesMethodSubscriber extends AbstractEnvAwareMethodSubscriber
{
    protected function methodName(): string
    {
        return 'resources';
    }

    protected function supports(ManifestMethodCalledEvent $event): bool
    {
        $manifest = $event->manifest();

        return $manifest instanceof ContainerInterface || $manifest instanceof PersistentVolumeClaimInterface;
    }
}
