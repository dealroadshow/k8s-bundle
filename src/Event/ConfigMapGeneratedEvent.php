<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\ConfigMap;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ConfigMap\ConfigMapInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConfigMapGeneratedEvent extends Event
{
    const NAME = 'dealroadshow_k8s.manifest_generated.configMap';

    public function __construct(private ConfigMapInterface $manifest, private ConfigMap $configMap, private AppInterface $app)
    {
    }

    public function manifest(): ConfigMapInterface
    {
        return $this->manifest;
    }

    public function configMap(): ConfigMap
    {
        return $this->configMap;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
