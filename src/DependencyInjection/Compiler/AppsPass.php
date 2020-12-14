<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Util\Str;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AppsPass implements CompilerPassInterface
{
    const APP_TAG = 'dealroadshow_k8s.app';

    private array $aliasToClassNamesMap;
    private array $classNameToAliasMap;
    private Definition $registryDefinition;
    private array $appsConfig;

    /**
     * @var ReflectionClass[]
     */
    private array $appClasses;

    /**
     * @param ContainerBuilder $container
     *
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(AppInterface::class)
            ->addTag(self::APP_TAG);

        $this->registryDefinition = $container->getDefinition(AppRegistry::class);
        $this->appsConfig = $container->getParameter('dealroadshow_k8s.config.apps');

        $this->collectAppClasses($container);
        $this->createAliasToClassNamesMap();
        $this->createClassNameToAliasMap();
        $this->registerByConfig($container);
        $this->registerByNames($container);
        $this->clearAppClassesDefinitions($container);
    }

    private function registerByConfig(ContainerBuilder $container): void
    {
        foreach ($this->appsConfig as $appAlias => $config) {
            $class = $config['class'] ?? null;
            if (null === $class) {
                if (!array_key_exists($appAlias, $this->aliasToClassNamesMap)) {
                    throw new InvalidConfigurationException(
                        sprintf(
                            'App "%s" cannot be found by name and does not specify "class" property in config.',
                            $appAlias
                        )
                    );
                }
                $classNames = $this->aliasToClassNamesMap[$appAlias];
                if (count($classNames) > 1) {
                    $messageTemplate = <<<'MESSAGE'
                    App alias "%s" is ambiguous, since it can point to app classes "%s".
                    Please specify "class" property for this alias explicitly.
                    MESSAGE;
                    $messageTemplate = str_replace(PHP_EOL, ' ', $messageTemplate);
                    $message = sprintf(
                        $messageTemplate,
                        $appAlias,
                        implode('", "', $classNames)
                    );
                    throw new InvalidConfigurationException($message);
                }
                $class = $classNames[0];
            }

            $this->createNewDefinition($class, $appAlias, $container);
        }
    }

    private function registerByNames(ContainerBuilder $container): void
    {
        foreach ($this->appClasses as $className => $class) {
            $alias = $this->classNameToAliasMap[$className];
            if (array_key_exists($alias, $this->appsConfig)) {
                // App is configured explicitly and was registered in registerByConfig() method
                continue;
            }

            $this->createNewDefinition($class, $alias, $container);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function createClassNameToAliasMap(): void
    {
        $this->classNameToAliasMap = [];
        foreach ($this->appClasses as $className => $class) {
            $alias = $class->getMethod('name')->invoke(null);
            $this->classNameToAliasMap[$class->getName()] = $alias;
        }
    }

    /**
     * @throws ReflectionException
     */
    private function createAliasToClassNamesMap(): void
    {
        $this->aliasToClassNamesMap = [];
        foreach ($this->appClasses as $className => $class) {
            $alias = $class->getMethod('name')->invoke(null);
            $this->aliasToClassNamesMap[$alias][] = $class->getName();
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws ReflectionException
     */
    private function collectAppClasses(ContainerBuilder $container): void
    {
        $this->appClasses = [];
        foreach ($container->findTaggedServiceIds(self::APP_TAG) as $id => $tags) {
            if (!class_exists($id)) {
                continue;
            }
            $class = new ReflectionClass($id);
            if (!$class->implementsInterface(AppInterface::class)) {
                throw new LogicException(
                    sprintf(
                        'Only %s instances must be tagged with tag "%s"',
                        AppInterface::class,
                        self::APP_TAG
                    )
                );
            }
            $this->appClasses[$class->getName()] = $class;
        }
    }

    private function createNewDefinition(string $class, string $alias, ContainerBuilder $container): void
    {
        $appDefinition = $container->getDefinition($class);
        $config = $this->appsConfig[$alias] ?? ['enabled' => true];
        if (!$config['enabled']) {
            return;
        }

        $newDefinition = clone $appDefinition;
        $newDefinition->addTag(self::APP_TAG, ['alias' => $alias]);
        $id = 'dealroadshow_k8s.apps.'.Str::underscored($alias);
        $container->setDefinition($id, $newDefinition);

        $this->registryDefinition->addMethodCall('add', [new Reference($id)]);
    }

    private function clearAppClassesDefinitions(ContainerBuilder $container): void
    {
        foreach ($this->appClasses as $className => $class) {
            $container->removeDefinition($className);
        }
    }
}
