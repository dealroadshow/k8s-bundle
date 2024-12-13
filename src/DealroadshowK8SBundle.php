<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle;

use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AutoSetReplicasPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AutoSetResourcesPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\EnabledManifestsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\LocalizationStrategyPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestGeneratorContextsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\MiddlewarePass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\RemoveAutowiredAppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ResourceMakersPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\SetAppConfigPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\DealroadshowK8SExtension;
use Dealroadshow\Bundle\K8SBundle\EnvManagement\DIContainerRegistry;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DealroadshowK8SBundle extends Bundle
{
    public function boot(): void
    {
        $this->validateManifestsConfigs();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new AppsPass())
            ->addCompilerPass(pass: new EnabledManifestsPass(), priority: -8)
            ->addCompilerPass(pass: new SetAppConfigPass(), type: PassConfig::TYPE_OPTIMIZE, priority: -8)
            ->addCompilerPass(pass: new ManifestsPass(), type: PassConfig::TYPE_OPTIMIZE, priority: -16)
            ->addCompilerPass(pass: new ManifestGeneratorContextsPass())
            ->addCompilerPass(pass: new MiddlewarePass())
            ->addCompilerPass(pass: new ResourceMakersPass())
            ->addCompilerPass(pass: new RemoveAutowiredAppsPass(), type: PassConfig::TYPE_REMOVE)
            ->addCompilerPass(pass: new AutoSetReplicasPass(), priority: -32)
            ->addCompilerPass(pass: new AutoSetResourcesPass(), priority: -32)
            ->addCompilerPass(pass: new LocalizationStrategyPass())
        ;
    }

    public function getContainerExtension(): DealroadshowK8SExtension
    {
        return new DealroadshowK8SExtension();
    }

    public function setContainer(ContainerInterface|null $container = null): void
    {
        $this->container = $container;
        if (null !== $container) {
            DIContainerRegistry::set($container);
        }
    }

    private function validateManifestsConfigs(): void
    {
        /** @var ManifestRegistry $manifestRegistry */
        $manifestRegistry = $this->container->get(ManifestRegistry::class);
        $appsConfigs = $this->container->getParameter('dealroadshow_k8s.config.apps');

        foreach ($appsConfigs as $appAlias => $appConfig) {
            foreach ($appConfig['manifests'] ?? [] as $manifestShortName => $manifestConfig) {
                if (!is_string($manifestShortName) || $manifestConfig['virtual'] ?? false) {
                    continue;
                }

                $manifest = $manifestRegistry
                    ->query($appAlias)
                    ->shortName($manifestShortName)
                    ->getFirstResult();

                if (null !== $manifest) {
                    continue;
                }

                throw new InvalidConfigurationException(
                    sprintf(
                        'There is a configuration section for manifest "%s" in manifests config in app "%s", '
                        .'however there is no manifest with such short name in this app. Did you make a typo?',
                        $manifestShortName,
                        $appAlias
                    )
                );
            }
        }
    }
}
