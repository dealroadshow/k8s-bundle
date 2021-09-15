<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Registry;

use Dealroadshow\K8S\APIResourceInterface;

class APIResourceRegistry
{
    private $resources = [];

    /**
     * @return APIResourceInterface[]
     */
    public function all(): iterable
    {
        foreach ($this->resources as $kind => $resources) {
            foreach ($resources as $resource) {
                yield $kind => $resource;
            }
        }
    }

    public function add(APIResourceInterface $apiResource): void
    {
        $this->resources[$apiResource::KIND][$apiResource->metadata()->getName()] = $apiResource;
    }

    public function get(string $name, string $kind): APIResourceInterface
    {
        return $this->resources[$kind][$name];
    }

    public function has(string $name, string $kind): bool
    {
        return ($this->resources[$kind][$name] ?? null) !== null;
    }
}
