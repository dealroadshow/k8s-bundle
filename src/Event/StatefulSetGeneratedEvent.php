<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\Apps\StatefulSet;
use Dealroadshow\K8S\APIResourceInterface;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\StatefulSet\StatefulSetInterface;
use Symfony\Contracts\EventDispatcher\Event;

class StatefulSetGeneratedEvent extends Event implements ManifestGeneratedEventInterface
{
    const NAME = 'dealroadshow_k8s.manifest_generated.stateful_set';

    public function __construct(private StatefulSetInterface $manifest, private StatefulSet $sts, private AppInterface $app)
    {
    }

    public function manifest(): StatefulSetInterface
    {
        return $this->manifest;
    }

    public function apiResource(): APIResourceInterface
    {
        return $this->sts;
    }

    public function statefulSet(): StatefulSet
    {
        return $this->sts;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
