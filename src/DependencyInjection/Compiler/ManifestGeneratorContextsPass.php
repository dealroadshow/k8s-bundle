<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context\ContextInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ManifestGeneratorContextsPass implements CompilerPassInterface
{
    const CONTEXT_TAG = 'dealroadshow_k8s.manifest_generator.context';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ContextInterface::class)
            ->addTag(self::CONTEXT_TAG);
    }
}
