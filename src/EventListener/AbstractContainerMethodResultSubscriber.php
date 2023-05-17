<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ContainerMethodCalledEvent;

abstract class AbstractContainerMethodResultSubscriber extends AbstractMethodResultSubscriber
{
    protected static function eventNames(): iterable
    {
        return [ContainerMethodCalledEvent::NAME];
    }
}
