<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Container\ResourcePolicyApplier;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ResourcesConfigurator;
use Dealroadshow\K8S\Framework\Core\Persistence\PersistentVolumeClaimInterface;
use Dealroadshow\K8S\Framework\Core\Persistence\PvcResourcesConfigurator;
use Dealroadshow\K8S\Framework\Event\ContainerMethodCalledEvent;
use Dealroadshow\K8S\Framework\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodCalledEventInterface;

class ResourcesPoliciesSubscriber extends AbstractMethodResultSubscriber
{
    public function __construct(
        private readonly ResourcePolicyApplier $resourcePolicyApplier,
        private readonly bool $containerResourcesPoliciesEnabled,
        private readonly string $containerResourcesPoliciesEnv,
    ) {
    }

    protected function supports(ProxyableMethodCalledEventInterface $event): bool
    {
        $proxyable = $event->proxyable();

        return $this->containerResourcesPoliciesEnabled
            && ($proxyable instanceof ContainerInterface || $proxyable instanceof PersistentVolumeClaimInterface)
            && 'resources' === $event->methodName();
    }

    protected function afterMethod(ProxyableMethodCalledEventInterface $event): void
    {
        /** @var ContainerInterface|PersistentVolumeClaimInterface $proxyable */
        $proxyable = $event->proxyable();
        /** @var ResourcesConfigurator|PvcResourcesConfigurator $resources */
        $resources = $event->methodParams()['resources'];
        $this->resourcePolicyApplier->apply($proxyable, $resources, $this->containerResourcesPoliciesEnv);
    }

    protected static function eventNames(): iterable
    {
        return [
            ManifestMethodCalledEvent::NAME,
            ContainerMethodCalledEvent::NAME,
        ];
    }
}
