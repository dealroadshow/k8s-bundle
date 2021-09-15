<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Middleware;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodEvent;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodPrefixMiddlewareInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodSuffixMiddlewareInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventDispatcherBridgeMiddleware implements ManifestMethodPrefixMiddlewareInterface, ManifestMethodSuffixMiddlewareInterface
{
    public function __construct(private EventDispatcherInterface $dispatcher)
    {
    }

    public function supports(ManifestInterface $manifest, string $methodName, array $params): bool
    {
        return true;
    }

    public static function priority(): int
    {
        return 0;
    }

    public function beforeMethodCall(ManifestInterface $proxy, string $methodName, array $params, mixed &$returnValue): void
    {
        $event = new ManifestMethodEvent(
            proxy: $proxy,
            methodName: $methodName,
            methodParams: $params,
        );
        $this->dispatcher->dispatch($event, ManifestMethodEvent::NAME);
        $valueToReturn = $event->getReturnValue();
        if (null !== $valueToReturn) {
            $returnValue = $valueToReturn;
        }
    }

    public function afterMethodCall(ManifestInterface $proxy, string $methodName, array $params, mixed $returnedValue, mixed &$returnValue): void
    {
        $event = new ManifestMethodCalledEvent(
            proxy: $proxy,
            methodName: $methodName,
            methodParams: $params,
            returnedValue: $returnedValue,
        );
        $this->dispatcher->dispatch($event, ManifestMethodCalledEvent::NAME);
        $valueToReturn = $event->getReturnValue();
        if (null !== $valueToReturn) {
            $returnValue = $valueToReturn;
        }
    }
}
