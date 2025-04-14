<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

class DummyReplicasPolicyConfigurator implements ReplicasPolicyConfiguratorInterface
{
    public function configure(ReplicasPolicyRegistry $policies): void
    {
    }
}
