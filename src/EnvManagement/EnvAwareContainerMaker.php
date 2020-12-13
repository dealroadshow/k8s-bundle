<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

use Dealroadshow\K8S\Data\Collection\VolumeList;
use Dealroadshow\K8S\Data\Container;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\Container\ContainerMaker;
use Dealroadshow\K8S\Framework\Core\Container\ContainerMakerInterface;
use Dealroadshow\K8S\Framework\Core\Container\Resources\ResourcesConfigurator;
use Dealroadshow\K8S\Framework\Util\Str;
use ReflectionException;
use ReflectionObject;

class EnvAwareContainerMaker implements ContainerMakerInterface
{
    public function __construct(private ContainerMaker $maker, private string $env)
    {
    }

    /**
     * @param ContainerInterface $builder
     * @param VolumeList         $volumes
     * @param AppInterface       $app
     *
     * @return Container
     * @throws ReflectionException
     */
    public function make(ContainerInterface $builder, VolumeList $volumes, AppInterface $app): Container
    {
        $container = $this->maker->make($builder, $volumes, $app);

        $envSpecificResourcesMethod = 'resources'.Str::asClassName($this->env);
        $class = new ReflectionObject($builder);
        if (!$class->hasMethod($envSpecificResourcesMethod)) {
            return $container;
        }

        $method = $class->getMethod($envSpecificResourcesMethod);
        $params = $method->getParameters();
        if (1 !== count($params) || !$params[0]->hasType() || ResourcesConfigurator::class !== $params[0]->getType()) {
            return $container;
        }

        $resources = new ResourcesConfigurator($container->resources());
        $method->invoke($builder, $resources);

        return $container;
    }
}