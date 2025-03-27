<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Container;

use Dealroadshow\K8S\Api\Core\V1\ResourceRequirements;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Container\Resources\CPU;
use Dealroadshow\K8S\Framework\Core\Container\Resources\Memory;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ResourcesConfigurator;
use Dealroadshow\K8S\Framework\Core\Persistence\PersistentVolumeClaimInterface;
use Dealroadshow\K8S\Framework\Core\Persistence\PvcResourcesConfigurator;
use Dealroadshow\K8S\Framework\Util\PropertyAccessor;

class ResourcePolicyApplier
{
    public function __construct(private ResourcePolicyRegistry $registry)
    {
    }

    public function apply(ContainerInterface|PersistentVolumeClaimInterface $container, ResourcesConfigurator|PvcResourcesConfigurator $resources, string $env): void
    {
        $class = new \ReflectionClass($container);
        $methodName = 'resources'.ucfirst($env);
        if ($class->hasMethod($methodName)) {
            $class->getMethod($methodName)->invoke($container, $resources);

            return;
        }

        $this->applyPolicy($resources, $this->registry->whenEnv($env));
    }

    private function applyPolicy(ResourcesConfigurator|PvcResourcesConfigurator $resources, ResourcePolicy $policy): void
    {
        /** @var ResourceRequirements $defaults */
        $defaults = PropertyAccessor::get($policy->defaults, 'resources');

        if ($cpuRequests = $defaults->requests()->get('cpu')) {
            $resources->requestCPU(CPU::fromString($cpuRequests));
        }
        if ($cpuLimits = $defaults->limits()->get('cpu')) {
            $resources->limitCPU(CPU::fromString($cpuLimits));
        }
        if ($memoryRequests = $defaults->requests()->get('memory')) {
            $resources->requestMemory(Memory::fromString($memoryRequests));
        }
        if ($memoryLimits = $defaults->limits()->get('memory')) {
            $resources->limitMemory(Memory::fromString($memoryLimits));
        }
        if ($storageRequests = $defaults->requests()->get('storage')) {
            $resources->requestStorage(Memory::fromString($storageRequests));
        }
        if ($storageLimits = $defaults->limits()->get('storage')) {
            $resources->limitStorage(Memory::fromString($storageLimits));
        }
    }
}
