<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodEvent;
use Dealroadshow\Bundle\K8SBundle\EventListener\Traits\ApplyWrappersTrait;
use ReflectionException;

class BeforeMethodSubscriber extends AbstractMethodSubscriber
{
    use ApplyWrappersTrait;

    private const NO_RETURN_VALUE = 'BEFORE-METHOD-SUBSCRIBER-NO-RETURN-VALUE';

    public function __construct(private string $env)
    {
    }

    protected function supports(ManifestMethodEvent $event): bool
    {
        return true;
    }

    /**
     * @throws ReflectionException
     */
    protected function beforeMethod(ManifestMethodEvent $event): void
    {
        $returnValue = self::NO_RETURN_VALUE;

        $this->applyWrappers(
            manifest: $event->manifest(),
            methodName: $event->methodName(),
            params: $event->methodParams(),
            attributeClass: BeforeMethod::class,
            returnValue: $returnValue,
        );

        if (self::NO_RETURN_VALUE !== $returnValue) {
            $event->setReturnValue($returnValue);
        }
    }
}
