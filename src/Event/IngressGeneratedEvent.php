<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\Extensions\Ingress;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Ingress\IngressInterface;
use Symfony\Contracts\EventDispatcher\Event;

class IngressGeneratedEvent extends Event
{
    const NAME = 'dealroadshow_k8s.manifest_generated.ingress';

    public function __construct(private IngressInterface $manifest, private Ingress $ingress, private AppInterface $app)
    {
    }

    public function manifest(): IngressInterface
    {
        return $this->manifest;
    }

    public function ingress(): Ingress
    {
        return $this->ingress;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
