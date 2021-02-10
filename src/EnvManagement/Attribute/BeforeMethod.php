<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class BeforeMethod
{
    public function __construct(private string $methodName, private array $forEnvs)
    {
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return string[]
     */
    public function envs(): array
    {
        return $this->forEnvs;
    }
}
