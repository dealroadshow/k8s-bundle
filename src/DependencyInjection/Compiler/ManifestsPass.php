<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ManifestsPass implements CompilerPassInterface
{
    const MANIFEST_TAG = 'dealroadshow_k8s.manifest';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ManifestInterface::class)
            ->addTag(self::MANIFEST_TAG);
    }
}
