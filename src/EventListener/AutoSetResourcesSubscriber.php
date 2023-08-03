<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Util\PropertyAccessUtil;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ContainerResourcesInterface;
use Dealroadshow\K8S\Framework\Core\Container\Resources\CPU;
use Dealroadshow\K8S\Framework\Core\Container\Resources\Memory;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Core\Job\JobInterface;
use Dealroadshow\K8S\Framework\Core\StatefulSet\StatefulSetInterface;
use Dealroadshow\K8S\Framework\Event\ContainerMethodEvent;
use Dealroadshow\K8S\Framework\Event\ManifestMethodEvent;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodEventInterface;

class AutoSetResourcesSubscriber extends AbstractMethodSubscriber
{
    protected function supports(ProxyableMethodEventInterface $event): bool
    {
        $manifest = $event->proxyable();
        $supportedWorkload = $manifest instanceof DeploymentInterface
            || $manifest instanceof JobInterface
            || $manifest instanceof StatefulSetInterface;

        return $supportedWorkload && $manifest instanceof ContainerInterface && 'resources' === $event->methodName();
    }

    protected function beforeMethod(ProxyableMethodEventInterface $event): void
    {
        /** @var DeploymentInterface|StatefulSetInterface $manifest */
        $manifest = $event->proxyable();
        /** @var AppInterface $app */
        $app = PropertyAccessUtil::getPropertyValue($manifest, 'app');
        $config = $app->manifestConfig($manifest::shortName())['resources'] ?? [];

        /** @var ContainerResourcesInterface $resources */
        $resources = $event->methodParams()['resources'];

        $cpuRequests = $config['requests']['cpu'] ?? null;
        $cpuLimits = $config['limits']['cpu'] ?? null;
        if ($cpuRequests) {
            $resources->requestCPU(CPU::fromString($cpuRequests));
        }
        if ($cpuLimits) {
            $resources->limitCPU(CPU::fromString($cpuLimits));
        }

        $memoryRequests = $config['requests']['memory'] ?? null;
        $memoryLimits = $config['limits']['memory'] ?? null;
        if ($memoryRequests) {
            $resources->requestMemory(Memory::fromString($memoryRequests));
        }
        if ($memoryLimits) {
            $resources->limitMemory(Memory::fromString($memoryLimits));
        }

        $storageRequests = $config['requests']['storage'] ?? null;
        $storageLimits = $config['limits']['storage'] ?? null;
        if ($storageRequests) {
            $resources->requestStorage(Memory::fromString($storageRequests));
        }
        if ($storageLimits) {
            $resources->limitStorage(Memory::fromString($storageLimits));
        }

        if (!empty($config['requests']) || !empty($config['limits'])) {
            // We don't want ambiguous behavior, so only one way of specifying resources should be used:
            // if resources are automatically set from config, they should not be set by method
            $event->setReturnValue(null); // This is done to prevent method body
        }
    }

    protected static function eventNames(): iterable
    {
        yield ManifestMethodEvent::NAME;
        yield ContainerMethodEvent::NAME;
    }
}
