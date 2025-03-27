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
    public function __construct(string $env, private bool $containerResourcesPoliciesEnabled)
    {
        parent::__construct($env);
    }

    protected function methodName(): string
    {
        return 'resources';
    }

    protected function supports(ProxyableMethodCalledEventInterface $event): bool
    {
        // This subscriber must not work when container resources policies are used, since this is alternative approach to setting resources.
        if (!$this->containerResourcesPoliciesEnabled) {
            return false;
        }

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
