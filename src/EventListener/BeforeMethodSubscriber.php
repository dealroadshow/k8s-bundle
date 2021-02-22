<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodEvent;
use Dealroadshow\Bundle\K8SBundle\EventListener\Traits\ApplyWrappersTrait;
use ReflectionException;

class BeforeMethodSubscriber extends AbstractMethodSubscriber
{
    use ApplyWrappersTrait;

    protected function supports(ManifestMethodEvent $event): bool
    {
        return true;
    }

    /**
     * @param ManifestMethodEvent $event
     *
     * @throws ReflectionException
     */
    protected function beforeMethod(ManifestMethodEvent $event): void
    {
        $this->applyWrappers(
            manifest: $event->manifest(),
            methodName: $event->methodName(),
            params: $event->methodParams(),
            attributeClass: BeforeMethod::class
        );
    }
}
