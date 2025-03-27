<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

class ResourcePolicyRegistry
{
    /**
     * @var array <string, ResourcePolicy> where key is an environment name, like "dev", "prod", etc.
     */
    private array $policies = [];

    public function whenEnv(string $env): ResourcePolicy
    {
        if (!array_key_exists($env, $this->policies)) {
            $this->policies[$env] = new ResourcePolicy();
        }

        return $this->policies[$env];
    }
}
