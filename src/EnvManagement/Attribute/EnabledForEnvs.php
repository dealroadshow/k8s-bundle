<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EnabledForEnvs
{
    /**
     * @var string[]
     */
    private array $envs;

    public function __construct(string ...$envs)
    {
        $this->envs = $envs;
    }

    /**
     * @return string[]
     */
    public function envs(): array
    {
        return $this->envs;
    }
}
