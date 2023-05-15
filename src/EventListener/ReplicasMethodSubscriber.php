<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

class ReplicasMethodSubscriber extends AbstractEnvAwareManifestMethodSubscriber
{
    public function methodName(): string
    {
        return 'replicas';
    }
}
