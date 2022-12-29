<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

use Dealroadshow\K8S\Data\Collection\VolumeList;
use Dealroadshow\K8S\Data\Container;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Container\ContainerMaker;
use Dealroadshow\K8S\Framework\Core\Container\ContainerMakerInterface;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ResourcesConfigurator;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use Dealroadshow\K8S\Framework\Util\Str;

class EnvAwareContainerMaker implements ContainerMakerInterface
{
    public function __construct(private ContainerMaker $maker, private string $env)
    {
    }

    /**
     * @throws \ReflectionException
     */
    public function make(ContainerInterface $manifest, VolumeList $volumes, AppInterface $app): Container
    {
        $container = $this->maker->make($manifest, $volumes, $app);
        $resources = new ResourcesConfigurator($container->resources());

        $class = new \ReflectionObject($manifest);
        $resourcesMethod = $class->getMethod('resources');
        $envSpecificResourcesMethodName = 'resources'.Str::asClassName($this->env);
        if (!$class->hasMethod($envSpecificResourcesMethodName)) {
            return $container;
        }

        $envSpecificResourcesMethod = $class->getMethod($envSpecificResourcesMethodName);
        if (!ReflectionUtil::sameSignature($resourcesMethod, $envSpecificResourcesMethod)) {
            return $container;
        }

        $envSpecificResourcesMethod->invoke($manifest, $resources);

        return $container;
    }
}
