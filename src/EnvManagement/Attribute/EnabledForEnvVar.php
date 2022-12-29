<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EnabledForEnvVar
{
    public function __construct(public readonly string $envVarName)
    {
    }
}