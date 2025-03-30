<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ProxyableMethodCalledEventInterface;

class ReplicasMethodSubscriber extends AbstractEnvAwareManifestMethodSubscriber
{
    public function __construct(string $env, private readonly bool $deploymentReplicasPoliciesEnabled)
    {
        parent::__construct($env);
    }

    public function methodName(): string
    {
        return 'replicas';
    }

    protected function supports(ProxyableMethodCalledEventInterface $event): bool
    {
        return !$this->deploymentReplicasPoliciesEnabled;
    }
}
