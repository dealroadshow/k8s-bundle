<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Core\Container\ContainerInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use ReflectionObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This subscriber makes sure that AbstractContainerDeployment instance and other
 * classes that have method containers() and being containers themselves, returns
 * proxy from method "containers()". This allows to use env-aware version
 * of ContainerInterface's methods, like "resources()" etc.
 */
class ContainersMethodSubscriber implements EventSubscriberInterface
{
    private const METHOD_NAME = 'containers';

    public function wrapContainersMethod(ManifestMethodCalledEvent $event): void
    {
        $manifest = $event->manifest();
        if (!$manifest instanceof ContainerInterface || self::METHOD_NAME !== $event->methodName()) {
            return;
        }

        $class = new ReflectionObject($manifest);
        if (!$class->hasMethod(self::METHOD_NAME)) {
            return;
        }

        $containers = $event->returnedValue();
        $event->setReturnValue($this->replaceManifestWithProxy($event->proxy(), $manifest, $containers));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManifestMethodCalledEvent::NAME => 'wrapContainersMethod',
        ];
    }

    private function replaceManifestWithProxy(ManifestInterface $proxy, ManifestInterface $manifest, iterable $containers): iterable
    {
        foreach ($containers as $container) {
            if ($manifest === $container) {
                yield $proxy;
            } else {
                yield $container;
            }
        }
    }
}
