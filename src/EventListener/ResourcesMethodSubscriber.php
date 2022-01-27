<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Persistence\PersistentVolumeClaimInterface;
use Dealroadshow\K8S\Framework\Event\ManifestMethodCalledEvent;

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
