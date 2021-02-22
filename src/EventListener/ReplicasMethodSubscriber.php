<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

class ReplicasMethodSubscriber extends AbstractEnvAwareMethodSubscriber
{
    public function methodName(): string
    {
        return 'replicas';
    }
}
