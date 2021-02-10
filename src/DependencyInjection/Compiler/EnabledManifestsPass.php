<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits\EnabledForEnvTrait;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EnabledManifestsPass implements CompilerPassInterface
{
    use EnabledForEnvTrait;

    /**
     * @param ContainerBuilder $container
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(ManifestsPass::MANIFEST_TAG);
        $env = $container->getParameter('kernel.environment');
        foreach ($ids as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = new ReflectionClass($definition->getClass());
            if (!$this->enabledForCurrentEnv($class, $env)) {
                $container->removeDefinition($id);
            }
        }
    }
}
