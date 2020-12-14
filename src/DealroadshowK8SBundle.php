<?php

namespace Dealroadshow\Bundle\K8SBundle;

use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\EnabledAppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestGeneratorContextsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\MiddlewarePass;
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
            ->addCompilerPass(new AppsPass())
            ->addCompilerPass(pass: new EnabledAppsPass(), priority: -8)
            ->addCompilerPass(pass: new ManifestsPass(), priority: -16)
            ->addCompilerPass(new ManifestGeneratorContextsPass())
            ->addCompilerPass(new MiddlewarePass())
            ->addCompilerPass(new ResourceMakersPass())
        ;
    }

    public function getContainerExtension(): DealroadshowK8SExtension
    {
        return new DealroadshowK8SExtension();
    }
}
