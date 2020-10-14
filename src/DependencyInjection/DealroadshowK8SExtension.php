<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context\ContextInterface;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestGeneratorContextsPass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AppsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ResourceMakersPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ManifestsPass;
use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\ProjectsPass;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use Dealroadshow\K8S\Framework\ResourceMaker\ResourceMakerInterface;
use Throwable;

class DealroadshowK8SExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $this->setupAutoconfiguration($container);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('dealroadshow_k8s.yaml');

        $this->setupCodeDir($config, $container);
        $this->setupManifestsDir($config, $container);
        $container->setParameter('dealroadshow_k8s.class_templates_dir', __DIR__.'/../Resources/class-templates');
        $container->setParameter('dealroadshow_k8s.namespace_prefix', $config['namespace_prefix']);
    }

    public function getAlias()
    {
        return 'dealroadshow_k8s';
    }

    private function setupAutoconfiguration(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ProjectInterface::class)
            ->addTag(ProjectsPass::PROJECT_TAG);

        $container->registerForAutoconfiguration(AppInterface::class)
            ->addTag(AppsPass::APP_TAG);

        $container->registerForAutoconfiguration(ManifestInterface::class)
            ->addTag(ManifestsPass::MANIFEST_TAG);

        $container->registerForAutoconfiguration(ResourceMakerInterface::class)
            ->addTag(ResourceMakersPass::RESOURCE_MAKER_TAG);

        $container->registerForAutoconfiguration(ContextInterface::class)
            ->addTag(ManifestGeneratorContextsPass::CONTEXT_TAG);
    }

    private function setupCodeDir(array $config, ContainerBuilder $container): void
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
            } catch (Throwable $e) {}
        }
        $container->setParameter('dealroadshow_k8s.code_dir', $codeDir);
    }

    private function setupManifestsDir(array $config, ContainerBuilder $container): void
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
