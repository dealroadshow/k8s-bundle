<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Persistence\PersistentVolumeClaimInterface;
use Dealroadshow\K8S\Framework\Event\ContainerMethodCalledEvent;
use Dealroadshow\K8S\Framework\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodCalledEventInterface;

class ResourcesMethodSubscriber extends AbstractEnvAwareMethodSubscriber
{
    protected function methodName(): string
    {
        return 'resources';
    }

    protected function supports(ProxyableMethodCalledEventInterface $event): bool
    {
        $proxyable = $event->proxyable();

        return $proxyable instanceof ContainerInterface || $proxyable instanceof PersistentVolumeClaimInterface;
    }

    protected static function eventNames(): iterable
    {
        return [
            ManifestMethodCalledEvent::NAME,
            ContainerMethodCalledEvent::NAME,
        ];
    }
}
