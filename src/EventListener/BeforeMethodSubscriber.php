<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\BeforeMethod;
use Dealroadshow\Bundle\K8SBundle\EventListener\Traits\ApplyWrappersTrait;
use Dealroadshow\K8S\Framework\Event\ContainerMethodEvent;
use Dealroadshow\K8S\Framework\Event\ManifestMethodEvent;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodEventInterface;

class BeforeMethodSubscriber extends AbstractMethodSubscriber
{
    use ApplyWrappersTrait;

    private const string NO_RETURN_VALUE = 'BEFORE-METHOD-SUBSCRIBER-NO-RETURN-VALUE';

    public function __construct(private string $env)
    {
    }

    protected function supports(ProxyableMethodEventInterface $event): bool
    {
        return true;
    }

    /**
     * @throws \ReflectionException
     */
    protected function beforeMethod(ProxyableMethodEventInterface $event): void
    {
        $returnValue = self::NO_RETURN_VALUE;

        $this->applyWrappers(
            proxyable: $event->proxyable(),
            methodName: $event->methodName(),
            params: $event->methodParams(),
            attributeClass: BeforeMethod::class,
            returnValue: $returnValue,
        );

        if (self::NO_RETURN_VALUE !== $returnValue) {
            $event->setReturnValue($returnValue);
        }
    }

    protected static function eventNames(): iterable
    {
        return [
            ManifestMethodEvent::NAME,
            ContainerMethodEvent::NAME,
        ];
    }
}
