<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForEnvs;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EnabledAppsPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(AppsPass::APP_TAG);
        $env = $container->getParameter('kernel.environment');
        foreach ($ids as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = new ReflectionClass($definition->getClass());
            $attributes = $class->getAttributes(EnabledForEnvs::class);
            $appEnabled = 0 === count($attributes); // Enabled by default if there is no attributes
            foreach ($attributes as $attribute) {
                /** @var EnabledForEnvs $attr */
                $attr = $attribute->newInstance();
                if (in_array($env, $attr->envs())) {
                    $appEnabled = true;
                    break;
                }
            }

            if (!$appEnabled) {
                $container->removeDefinition($id);
            }
        }
    }
}