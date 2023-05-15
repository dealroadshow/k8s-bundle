<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\K8S\Framework\Event\ManifestMethodCalledEvent;

class ReplicasMethodSubscriber extends AbstractEnvAwareMethodSubscriber
{
    public function methodName(): string
    {
        return 'replicas';
    }

    protected static function eventNames(): iterable
    {
        return [ManifestMethodCalledEvent::NAME];
    }
}
