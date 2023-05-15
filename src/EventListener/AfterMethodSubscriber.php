<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\AfterMethod;
use Dealroadshow\Bundle\K8SBundle\EventListener\Traits\ApplyWrappersTrait;
use Dealroadshow\K8S\Framework\Event\ContainerMethodCalledEvent;
use Dealroadshow\K8S\Framework\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodCalledEventInterface;

class AfterMethodSubscriber extends AbstractMethodResultSubscriber
{
    use ApplyWrappersTrait;

    private const NO_RETURN_VALUE = 'AFTER-METHOD-SUBSCRIBER-NO-RETURN-VALUE';

    public function __construct(private string $env)
    {
    }

    protected function supports(ProxyableMethodCalledEventInterface $event): bool
    {
        return true;
    }

    /**
     * @throws \ReflectionException
     */
    protected function afterMethod(ProxyableMethodCalledEventInterface $event): void
    {
        $returnValue = self::NO_RETURN_VALUE;

        $this->applyWrappers(
            proxyable: $event->proxyable(),
            methodName: $event->methodName(),
            params: $event->methodParams(),
            attributeClass: AfterMethod::class,
            returnValue: $returnValue,
        );

        if (self::NO_RETURN_VALUE !== $returnValue) {
            $event->setReturnValue($returnValue);
        }
    }

    protected static function eventNames(): iterable
    {
        return [
            ManifestMethodCalledEvent::NAME,
            ContainerMethodCalledEvent::NAME,
        ];
    }
}
