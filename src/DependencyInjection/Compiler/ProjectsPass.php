<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProjectsPass implements CompilerPassInterface
{
    const PROJECT_TAG = 'dealroadshow_k8s.project';

    public function process(ContainerBuilder $container)
    {
    }
}
