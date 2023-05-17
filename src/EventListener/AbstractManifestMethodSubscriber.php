<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ManifestMethodEvent;

abstract class AbstractManifestMethodSubscriber extends AbstractMethodSubscriber
{
    protected static function eventNames(): iterable
    {
        return [ManifestMethodEvent::NAME];
    }
}
