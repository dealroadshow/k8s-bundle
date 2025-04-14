<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

class DummyResourcePolicyConfigurator implements ResourcePolicyConfiguratorInterface
{
    public function configure(ResourcePolicyRegistry $policies): void
    {
    }
}
