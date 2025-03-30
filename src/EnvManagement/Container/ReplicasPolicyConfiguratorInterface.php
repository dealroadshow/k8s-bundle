<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

interface ReplicasPolicyConfiguratorInterface
{
    public function configure(ReplicasPolicyRegistry $policies): void;
}
