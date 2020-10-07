<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ManifestsPass implements CompilerPassInterface
{
    const MANIFEST_TAG = 'dealroadshow_k8s.manifest';

    public function process(ContainerBuilder $container)
    {
    }
}
