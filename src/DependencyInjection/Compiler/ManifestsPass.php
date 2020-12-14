<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use Dealroadshow\K8S\Framework\Util\Str;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ManifestsPass implements CompilerPassInterface
{
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
                    $registryDefinition->addMethodCall($alias, [new Reference($newId)]);
                }
            }
        }
    }

    private function createAppClassToAliasesMap(ContainerBuilder $container): array
    {
        $map = [];
        foreach ($container->findTaggedServiceIds(AppsPass::APP_TAG) as $id => $tags) {
            $definition = $container->getDefinition($id);
            $className = $definition->getClass();

            $alias = null;
            foreach ($tags as $tag) {
                if (array_key_exists('alias', $tag)) {
                    $alias = $tag['alias'];
                    break;
                }
            }
            if (null === $alias) {
                throw new LogicException(
                    sprintf(
                        '"%s" tag on app service "%s" does not have "alias" attribute.',
                        AppsPass::APP_TAG,
                        $id
                    )
                );
            }

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
