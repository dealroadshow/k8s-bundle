<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection;

use Dealroadshow\Bundle\K8SBundle\Checksum\Calculator\ChecksumCalculatorInterface;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ChecksumCalculatorPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\DefaultMetadataLabelsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\DefaultSelectorLabelsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\DefaultServiceSelectorPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestGeneratorContextsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\MiddlewarePass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ResourceMakersPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\ContainerResources\ResourcesReferenceResolver;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Middleware\ContainerImageMiddlewareInterface;
use Dealroadshow\K8S\Framework\ResourceMaker\ResourceMakerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Throwable;

class DealroadshowK8SExtension extends Extension
{
    private readonly ResourcesReferenceResolver $referenceResolver;

    public function __construct()
    {
        $this->referenceResolver = new ResourcesReferenceResolver();
    }

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('dealroadshow_k8s.yaml');

        $appsConfigs = array_column($configs, 'apps');
        $normalizedAppsConfigs = [];
        foreach ($appsConfigs as $i => $appsConfig) {
            foreach ($appsConfig as $appConfig) {
                $normalizedAppsConfigs[$i][$appConfig['alias']] = $appConfig;
            }
        }
        if (2 > count($normalizedAppsConfigs)) {
            $normalizedAppsConfigs[] = []; // array_replace_recursive will have enough arguments
        }

        // We use ArrayNode::ignoreExtraKeys() for app configs in Configuration class,
        // therefore Symfony will not merge app configs properly - thus we need to do this ourselves
        $finalAppsConfig = array_replace_recursive(...$normalizedAppsConfigs);

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $config['apps'] = array_replace_recursive($config['apps'], $finalAppsConfig);
        $this->loadInternal(
            $config,
            $container
        );
    }

    /**
     * @throws \Exception
     */
    private function loadInternal(array $config, ContainerBuilder $container): void
    {
        $this->setupAutoconfiguration($container);

        $this
            ->setupCodeDir($config, $container)
            ->setupManifestsDir($config, $container)
            ->setupFilters($config, $container)
            ->setupTemplatesDir($container)
            ->setupNamespacePrefix($config['namespace_prefix'], $container)
        ;

        $container->setParameter('dealroadshow_k8s.auto_set_resources', $config['auto_set_resources']);
        $container->setParameter('dealroadshow_k8s.auto_set_replicas', $config['auto_set_replicas']);

        $container->setParameter(DefaultMetadataLabelsPass::PARAM, $config[Configuration::PARAM_SET_DEFAULT_METADATA_LABELS]);
        $container->setParameter(DefaultSelectorLabelsPass::PARAM, $config[Configuration::PARAM_SET_DEFAULT_SELECTOR_LABELS]);
        $container->setParameter(DefaultServiceSelectorPass::PARAM, $config[Configuration::PARAM_SET_DEFAULT_SERVICE_SELECTOR]);

        foreach ($config['apps'] as $appAlias => $appConfig) {
            foreach ($appConfig['manifests'] as $shortName => $manifestConfig) {
                $resources = $manifestConfig['resources'] ?? [];

                foreach (['requests', 'limits'] as $resourcesKey) {
                    $resourcesSection = $resources[$resourcesKey] ?? [];
                    if (is_string($resourcesSection)) {
                        $config['apps'][$appAlias]['manifests'][$shortName]['resources'][$resourcesKey] = $this->referenceResolver->resolve($resourcesSection, $resourcesKey, $appAlias, $config['apps']);
                    }
                }
            }
        }

        $container->setParameter('dealroadshow_k8s.config.apps', $config['apps']);
    }

    public function getAlias(): string
    {
        return 'dealroadshow_k8s';
    }

    private function setupAutoconfiguration(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(AppInterface::class)
            ->addTag(AppsPass::APP_TAG);

        $container->registerForAutoconfiguration(ManifestInterface::class)
            ->addTag(ManifestsPass::MANIFEST_TAG);

        $container->registerForAutoconfiguration(ResourceMakerInterface::class)
            ->addTag(ResourceMakersPass::RESOURCE_MAKER_TAG);

        $container->registerForAutoconfiguration(ManifestGenerator\Context\ContextInterface::class)
            ->addTag(ManifestGeneratorContextsPass::CONTEXT_TAG);

        $container->registerForAutoconfiguration(ContainerImageMiddlewareInterface::class)
            ->addTag(MiddlewarePass::IMAGE_MIDDLEWARE_TAG);

        $container
            ->registerForAutoconfiguration(ChecksumCalculatorInterface::class)
            ->addTag(ChecksumCalculatorPass::CHECKSUM_CALCULATOR_TAG);
    }

    private function setupTemplatesDir(ContainerBuilder $container): static
    {
        $container->setParameter(
            'dealroadshow_k8s.class_templates_dir',
            __DIR__.'/../Resources/class-templates'
        );

        return $this;
    }

    private function setupNamespacePrefix(string $prefix, ContainerBuilder $container): void
    {
        $container->setParameter('dealroadshow_k8s.namespace_prefix', $prefix);
    }

    private function setupFilters(array $config, ContainerBuilder $container): static
    {
        $container->setParameter(
            'dealroadshow_k8s.filter.tags.include',
            $config['filterManifests']['byTags']['include']
        );
        $container->setParameter(
            'dealroadshow_k8s.filter.tags.exclude',
            $config['filterManifests']['byTags']['exclude']
        );

        return $this;
    }

    private function setupCodeDir(array $config, ContainerBuilder $container): static
    {
        $codeDir = $config['code_dir'] ?? null;
        if (null === $codeDir) {
            $srcDir = $this->getSrcDir($container);
            if (!file_exists($srcDir)) {
                throw $this->createExceptionForSrcDir('dealroadshow_k8s.code_dir', $srcDir);
            }
            $codeDir = $srcDir.DIRECTORY_SEPARATOR.'K8S';
        }
        if (!file_exists($codeDir)) {
            try {
                @mkdir($codeDir, 0o700, true);
            } catch (Throwable) {
            }
        }
        $container->setParameter('dealroadshow_k8s.code_dir', $codeDir);

        return $this;
    }

    private function setupManifestsDir(array $config, ContainerBuilder $container): static
    {
        $configParam = 'manifests_dir';
        $manifestsDir = $config[$configParam] ?? null;
        if (null === $manifestsDir) {
            $varDir = $container->getParameter('kernel.project_dir').DIRECTORY_SEPARATOR.'var';
            if (!file_exists($varDir)) {
                throw new \InvalidArgumentException(sprintf('Parameter "%s" is not set, default value is "var" directory, which was resolved to "%s", but no such directory exists.', $configParam, $varDir));
            }
            $manifestsDir = $varDir.DIRECTORY_SEPARATOR.'k8s-manifests';
        }
        if (!file_exists($manifestsDir)) {
            @mkdir($manifestsDir, 0o777, true);
        }
        $container->setParameter('dealroadshow_k8s.manifests_dir', $manifestsDir);

        return $this;
    }

    private function getSrcDir(ContainerBuilder $container): string
    {
        return $container->getParameter('kernel.project_dir').DIRECTORY_SEPARATOR.'src';
    }

    private function createExceptionForSrcDir(string $paramName, string $srcDir): InvalidConfigurationException
    {
        $errMessage = <<<ERR
            "$paramName" config value was not specified.
            Fallback value requires %kernel.project_dir%/src dir, which was resolved 
            to "$srcDir", but this directory does not exist. Please configure "$paramName" 
            config value explicitly as an absolute path or use standard "src" directory
            for your code.
            ERR;
        $errMessage = str_replace(PHP_EOL, ' ', $errMessage);

        return new InvalidConfigurationException($errMessage);
    }
}
