<?php

namespace Dealroadshow\Bundle\K8SBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ResourceMakersPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\DealroadshowK8SExtension;

class DealroadshowK8SBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ResourceMakersPass())
            ->addCompilerPass(new AppsPass());
    }

    public function getContainerExtension()
    {
        return new DealroadshowK8SExtension();
    }
}
