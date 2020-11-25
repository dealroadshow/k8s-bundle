<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\App\AppInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppsPass implements CompilerPassInterface
{
    const APP_TAG = 'dealroadshow_k8s.app';

    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(AppInterface::class)
            ->addTag(self::APP_TAG);
    }
}
