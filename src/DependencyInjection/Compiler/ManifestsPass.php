<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits\GetAppAliasTrait;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use Dealroadshow\K8S\Framework\Util\Str;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\TypedReference;

class ManifestsPass implements CompilerPassInterface
{
    use GetAppAliasTrait;

    const MANIFEST_TAG = 'dealroadshow_k8s.manifest';

    private array $appReflectionsCache = [];

    /**
     * @param ContainerBuilder $container
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ManifestInterface::class)
            ->addTag(self::MANIFEST_TAG);

        $registryDefinition = $container->getDefinition(ManifestRegistry::class);

        $classNameToAliasesMap = $this->createAppClassToAliasesMap($container);
        foreach ($container->findTaggedServiceIds(self::MANIFEST_TAG) as $id => $tags) {
            if (!class_exists($id)) {
                continue;
            }
            $manifestDefinition = $container->getDefinition($id);
            $manifestClass = new ReflectionClass($manifestDefinition->getClass());
            if (!$manifestClass->implementsInterface(ManifestInterface::class)) {
                throw new LogicException(
                    sprintf(
                        'Only %s instances must be tagged with tag "%s"',
                        ManifestInterface::class,
                        self::MANIFEST_TAG
                    )
                );
            }

            $manifestShortName = $manifestClass->getMethod('shortName')->invoke(null);
            $manifestKind = $manifestClass->getMethod('kind')->invoke(null);

            foreach ($classNameToAliasesMap as $className => $aliases) {
                $appClass = $this->getAppReflection($className);
                if (!str_starts_with($manifestDefinition->getClass(), $appClass->getNamespaceName())) {
                    // We need to find an app class, which "owns" this manifest
                    continue;
                }

                foreach ($aliases as $alias) {
                    $dedicatedManifestDefinition = clone $manifestDefinition;
                    $newId = sprintf(
                        'dealroadshow_k8s.apps.%s.manifests.%s_%s',
                        Str::underscored($alias),
                        Str::underscored($manifestShortName),
                        Str::underscored($manifestKind)
                    );
                    $container->setDefinition($newId, $dedicatedManifestDefinition);
                    $registryDefinition->addMethodCall('add', [$alias, new Reference($newId)]);

                    $this->autowireContainerClasses($container, $newId, $alias);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $manifestDefinitionId
     * @param string           $appAlias
     *
     * @throws ReflectionException
     */
    private function autowireContainerClasses(ContainerBuilder $container, string $manifestDefinitionId, string $appAlias)
    {
        $manifestDefinition = $container->getDefinition($manifestDefinitionId);
        foreach ($manifestDefinition->getArguments() as $name => $argument) {
            if (!$argument instanceof TypedReference) {
                continue;
            }
            $type = $argument->getType();
            if (!class_exists($type)) {
                continue;
            }
            $class = new ReflectionClass($type);
            if (!$class->implementsInterface(ContainerInterface::class)) {
                continue;
            }
            $containerDefinition = $container->getDefinition($class->getName());
            $newDefinition = clone $containerDefinition;
            $id = $manifestDefinitionId.'.containers.'.$name;
            $container->setDefinition($id, $newDefinition);
            $manifestDefinition->replaceArgument($name, new Reference($id));
            $this->autowireContainerClass($container, $id, $appAlias);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $containerDefinitionId
     * @param string           $appAlias
     *
     * @throws ReflectionException
     */
    private function autowireContainerClass(ContainerBuilder $container, string $containerDefinitionId, string $appAlias)
    {
        $containerDefinition = $container->getDefinition($containerDefinitionId);
        $appDefinitionId = AppsPass::appDefinitionId($appAlias);
        $appDefinition = $container->getDefinition($appDefinitionId);
        foreach ($containerDefinition->getArguments() as $name => $argument) {
            if (!$argument instanceof TypedReference) {
                continue;
            }
            $type = $argument->getType();
            if (!class_exists($type)) {
                continue;
            }
            $class = new ReflectionClass($type);
            if (!$class->implementsInterface(AppInterface::class)) {
                continue;
            }
            if ($class->getName() !== $appDefinition->getClass()) {
                continue;
            }
            $containerDefinition->replaceArgument($name, new Reference($appDefinitionId));
        }
    }

    private function createAppClassToAliasesMap(ContainerBuilder $container): array
    {
        $map = [];
        foreach ($container->findTaggedServiceIds(AppsPass::APP_TAG) as $id => $tags) {
            $definition = $container->getDefinition($id);
            $className = $definition->getClass();

            // Default (autowired) app services will be removed later in RemoveAutowiredAppsPass
            if ($id === $className) {
                continue;
            }

            $alias = $this->getAppAlias($id, $tags);

            $map[$className][] = $alias;
        }

        return $map;
    }

    /**
     * @param string $className
     *
     * @return ReflectionClass
     * @throws ReflectionException
     */
    private function getAppReflection(string $className): ReflectionClass
    {
        if (!array_key_exists($className, $this->appReflectionsCache)) {
            $this->appReflectionsCache[$className] = new ReflectionClass($className);
        }

        return $this->appReflectionsCache[$className];
    }
}
