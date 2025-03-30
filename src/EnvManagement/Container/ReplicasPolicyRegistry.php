<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

final class ReplicasPolicyRegistry
{
    /**
     * @var array <string, int> where key is an environment name, like "dev", "prod", etc. and value is the default replicas
     */
    private array $defaults = [];

    public function whenEnv(string $env, int $defaultReplicas): self
    {
        $this->defaults[$env] = $defaultReplicas;

        return $this;
    }

    public function getForEnv(string $env): int
    {
        return $this->defaults[$env];
    }

    public function hasForEnv(string $env): bool
    {
        return array_key_exists($env, $this->defaults);
    }
}
