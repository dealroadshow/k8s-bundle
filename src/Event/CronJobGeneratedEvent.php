<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\CronJob\CronJobInterface;
use Symfony\Contracts\EventDispatcher\Event;

class CronJobGeneratedEvent extends Event
{
    const NAME = 'dealroadshow_k8s.manifest_generated.cronJob';

    public function __construct(private CronJobInterface $manifest, private CronJob $cronJob, private AppInterface $app)
    {
    }

    public function manifest(): CronJobInterface
    {
        return $this->manifest;
    }

    public function cronJob(): CronJob
    {
        return $this->cronJob;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
