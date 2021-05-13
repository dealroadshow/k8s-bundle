<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ManifestsProcessedEvent extends Event
{
    const NAME = 'dealroadshow_k8s.manifests.processed';
}
