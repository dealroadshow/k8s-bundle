<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\Secret;
use Dealroadshow\K8S\APIResourceInterface;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Secret\SecretInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SecretGeneratedEvent extends Event implements ManifestGeneratedEventInterface
{
    const NAME = 'dealroadshow_k8s.manifest_generated.secret';

    public function __construct(private SecretInterface $manifest, private Secret $secret, private AppInterface $app)
    {
    }

    public function manifest(): SecretInterface
    {
        return $this->manifest;
    }

    public function apiResource(): APIResourceInterface
    {
        return $this->secret;
    }

    public function secret(): Secret
    {
        return $this->secret;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
