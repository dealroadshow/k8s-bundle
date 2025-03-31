<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Container\ReplicasPolicyConfiguratorInterface;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\Container\ReplicasPolicyRegistry;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Core\StatefulSet\StatefulSetInterface;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodCalledEventInterface;

class ReplicasPoliciesSubscriber extends AbstractManifestMethodResultSubscriber
{
    public function __construct(
        private readonly bool $deploymentReplicasPoliciesEnabled,
        private readonly string $deploymentReplicasPoliciesEnv,
        private readonly ReplicasPolicyRegistry $policies,
        ReplicasPolicyConfiguratorInterface $configurator,
    ) {
        $configurator->configure($policies);
    }

    protected function supports(ProxyableMethodCalledEventInterface $event): bool
    {
        $manifest = $event->proxyable();

        return $this->deploymentReplicasPoliciesEnabled &&
            ($manifest instanceof DeploymentInterface || $manifest instanceof StatefulSetInterface)
            && 'replicas' === $event->methodName();
    }

    protected function afterMethod(ProxyableMethodCalledEventInterface $event): void
    {
        $manifest = $event->proxyable();
        $class = new \ReflectionClass($manifest);
        $envAwareMethodName = 'replicas'.ucfirst($this->deploymentReplicasPoliciesEnv);
        if ($class->hasMethod($envAwareMethodName)) {
            $event->setReturnValue($class->getMethod($envAwareMethodName)->invoke($manifest));

            return;
        }

        if ($this->policies->hasForEnv($this->deploymentReplicasPoliciesEnv) && 0 !== $event->returnedValue()) {
            $event->setReturnValue($this->policies->getForEnv($this->deploymentReplicasPoliciesEnv));
        }
    }
}
