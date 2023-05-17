<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ContainerMethodEvent;

abstract class AbstractContainerMethodSubscriber extends AbstractMethodSubscriber
{
    protected static function eventNames(): iterable
    {
        return [ContainerMethodEvent::NAME];
    }
}
