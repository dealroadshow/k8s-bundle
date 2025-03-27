<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

interface ResourcePolicyConfiguratorInterface
{
    public function configure(ResourcePolicyRegistry $policies): void;
}
