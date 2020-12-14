<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestGeneratorContextsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\MiddlewarePass;
use Dealroadshow\K8S\Framework\Middleware\ContainerImageMiddlewareInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodMiddlewareInterface;
use Exception;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ResourceMakersPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestsPass;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\ResourceMaker\ResourceMakerInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Throwable;

class DealroadshowK8SExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('dealroadshow_k8s.yaml');

        $appsConfigs = array_column($configs, 'apps');
        if (2 > count($appsConfigs)) {
            $appsConfigs[] = []; // array_replace_recursive will have enough arguments
        }
        // We use ArrayNode::ignoreExtraKeys() for app configs in Configuration class,
        // therefore Symfony will not merge app configs properly - thus we need to do this ourselves
        $configs[count($configs) - 1]['apps'] = array_replace_recursive(...$appsConfigs);

        $this->loadInternal(
            $this->processConfiguration($this->getConfiguration($configs, $container), $configs),
            $container
        );
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    private function loadInternal(array $config, ContainerBuilder $container)
    {
        $this->setupAutoconfiguration($container);

        $this
            ->setupCodeDir($config, $container)
            ->setupManifestsDir($config, $container)
            ->setupFilters($config, $container)
            ->setupTemplatesDir($container)
            ->setupNamespacePrefix($config['namespace_prefix'], $container)
        ;

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

        $container->registerForAutoconfiguration(ManifestMethodMiddlewareInterface::class)
            ->addTag(MiddlewarePass::MANIFEST_MIDDLEWARE_TAG);
    }

    private function setupTemplatesDir(ContainerBuilder $container): static
    {
        $container->setParameter(
            'dealroadshow_k8s.class_templates_dir',
            __DIR__.'/../Resources/class-templates'
        );

        return $this;
    }

    private function setupNamespacePrefix(string $prefix, ContainerBuilder $container): static
    {
        $container->setParameter('dealroadshow_k8s.namespace_prefix', $prefix);

        return $this;
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
                @mkdir($codeDir, 0700, true);
            } catch (Throwable) {}
        }
        $container->setParameter('dealroadshow_k8s.code_dir', $codeDir);

        return $this;
    }

    private function setupManifestsDir(array $config, ContainerBuilder $container): static
    {
        $manifestsDir = $config['manifests_dir'] ?? null;
        if (null === $manifestsDir) {
            $srcDir = $this->getSrcDir($container);
            if (!file_exists($srcDir)) {
                throw $this->createExceptionForSrcDir(
                    'dealroadshow_k8s.manifests_dir',
                    $srcDir
                );
            }
            $manifestsDir = $srcDir.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'k8s-manifests';
        }
        if (!file_exists($manifestsDir)) {
            @mkdir($manifestsDir, 0777, true);
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
