<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EnabledForApps
{
    /**
     * @var string[]
     */
    private array $appAliases;

    public function __construct(string ...$appAliases)
    {
        $this->appAliases = $appAliases;
    }

    /**
     * @return string[]
     */
    public function appAliases(): array
    {
        return $this->appAliases;
    }
}
