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

    private function applyPolicy(ResourcesConfigurator|PvcResourcesConfigurator $resourcesConfigurator, ResourcePolicy $policy): void
    {
        /** @var ResourceRequirements $defaults */
        $defaults = PropertyAccessor::get($policy->defaults, 'resources');
        /** @var ResourceRequirements $resources */
        $resources = PropertyAccessor::get($resourcesConfigurator, 'resources');
        $requests = $resources->requests();
        $limits = $resources->limits();

        if ($defaultCpuRequests = $defaults->requests()->get('cpu')) {
            $defaultCpuRequests = CPU::fromString($defaultCpuRequests);
            if (!$requests->has('cpu')) {
                $resourcesConfigurator->requestCPU($defaultCpuRequests);
            } else {
                $existingCpuRequests = CPU::fromString($requests->get('cpu'));
                $cpuRequests = $existingCpuRequests->lowerThan($defaultCpuRequests) ? $existingCpuRequests : $defaultCpuRequests;
                $resourcesConfigurator->requestCPU($cpuRequests);
            }
        }

        if ($defaultCpuLimits = $defaults->limits()->get('cpu')) {
            $defaultCpuLimits = CPU::fromString($defaultCpuLimits);
            if (!$limits->has('cpu')) {
                $resourcesConfigurator->limitCPU($defaultCpuLimits);
            } else {
                $existingCpuLimits = CPU::fromString($limits->get('cpu'));
                $cpuLimits = $existingCpuLimits->lowerThan($defaultCpuLimits) ? $existingCpuLimits : $defaultCpuLimits;
                $resourcesConfigurator->limitCPU($cpuLimits);
            }
        }

        if ($defaultMemoryRequests = $defaults->requests()->get('memory')) {
            $defaultMemoryRequests = Memory::fromString($defaultMemoryRequests);
            if (!$requests->has('memory')) {
                $resourcesConfigurator->requestMemory($defaultMemoryRequests);
            } else {
                $existingMemoryRequests = Memory::fromString($requests->get('memory'));
                $memoryRequests = $existingMemoryRequests->lowerThan($defaultMemoryRequests) ? $existingMemoryRequests : $defaultMemoryRequests;
                $resourcesConfigurator->requestMemory($memoryRequests);
            }
        }

        if ($defaultMemoryLimits = $defaults->limits()->get('memory')) {
            $defaultMemoryLimits = Memory::fromString($defaultMemoryLimits);
            if (!$limits->has('memory')) {
                $resourcesConfigurator->limitMemory($defaultMemoryLimits);
            } else {
                $existingMemoryLimits = Memory::fromString($limits->get('memory'));
                $memoryLimits = $existingMemoryLimits->lowerThan($defaultMemoryLimits) ? $existingMemoryLimits : $defaultMemoryLimits;
                $resourcesConfigurator->limitMemory($memoryLimits);
            }
        }

        if ($defaultStorageRequests = $defaults->requests()->get('storage')) {
            $defaultStorageRequests = Memory::fromString($defaultStorageRequests);
            if (!$requests->has('storage')) {
                $resourcesConfigurator->requestStorage($defaultStorageRequests);
            } else {
                $existingStorageRequests = Memory::fromString($requests->get('storage'));
                $storageRequests = $existingStorageRequests->lowerThan($defaultStorageRequests) ? $existingStorageRequests : $defaultStorageRequests;
                $resourcesConfigurator->requestStorage($storageRequests);
            }
        }

        if ($defaultStorageLimits = $defaults->limits()->get('storage')) {
            $defaultStorageLimits = Memory::fromString($defaultStorageLimits);
            if (!$limits->has('storage')) {
                $resourcesConfigurator->limitStorage($defaultStorageLimits);
            } else {
                $existingStorageLimits = Memory::fromString($limits->get('storage'));
                $storageLimits = $existingStorageLimits->lowerThan($defaultStorageLimits) ? $existingStorageLimits : $defaultStorageLimits;
                $resourcesConfigurator->limitStorage($storageLimits);
            }
        }
    }
}
