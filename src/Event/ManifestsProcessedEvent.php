<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ManifestsProcessedEvent extends Event
{
    public const NAME = 'dealroadshow_k8s.manifests.processed';
}
