<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\Service;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Service\ServiceInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ServiceGeneratedEvent extends Event
{
    const NAME = 'dealroadshow_k8s.manifest_generated.service';

    public function __construct(private ServiceInterface $manifest, private Service $service, private AppInterface $app)
    {
    }

    public function manifest(): ServiceInterface
    {
        return $this->manifest;
    }

    public function service(): Service
    {
        return $this->service;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
