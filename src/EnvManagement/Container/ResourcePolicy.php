<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

use Dealroadshow\K8S\Api\Core\V1\ResourceRequirements;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ResourcesConfigurator;

readonly class ResourcePolicy
{
    public ResourcesConfigurator $defaults;

    public function __construct()
    {
        $this->defaults = new ResourcesConfigurator(new ResourceRequirements());
    }
}
