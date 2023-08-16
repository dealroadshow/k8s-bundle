<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class EnabledForContainerParameter
{
    public function __construct(public string $parameter)
    {
    }
}