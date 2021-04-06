<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AfterMethod
{
    public function __construct(private string $methodName, private array $forEnvs = [], private array $exceptEnvs = [])
    {
        if (!empty($this->forEnvs) && !empty($this->exceptEnvs)) {
            throw new \LogicException('Only one of forEnvs and exceptEnvs must be supplied');
        }
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return string[]
     */
    public function enabledForEnvs(): array
    {
        return $this->forEnvs;
    }

    /**
     * @return string[]
     */
    public function disabledForEnvs(): array
    {
        return $this->exceptEnvs;
    }
}
