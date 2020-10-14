<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProjectsPass implements CompilerPassInterface
{
    const PROJECT_TAG = 'dealroadshow_k8s.project';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ProjectInterface::class)
            ->addTag(self::PROJECT_TAG);
    }
}
