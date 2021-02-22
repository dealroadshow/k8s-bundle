<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\AfterMethod;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\Bundle\K8SBundle\EventListener\Traits\ApplyWrappersTrait;
use ReflectionException;

class AfterMethodSubscriber extends AbstractMethodResultSubscriber
{
    use ApplyWrappersTrait;

    protected function supports(ManifestMethodCalledEvent $event): bool
    {
        return true;
    }

    /**
     * @param ManifestMethodCalledEvent $event
     *
     * @throws ReflectionException
     */
    protected function afterMethod(ManifestMethodCalledEvent $event): void
    {
        $this->applyWrappers(
            manifest: $event->manifest(),
            methodName: $event->methodName(),
            params: $event->methodParams(),
            attributeClass: AfterMethod::class
        );
    }
}