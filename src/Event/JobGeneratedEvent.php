<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\API\Batch\Job;
use Dealroadshow\K8S\APIResourceInterface;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Job\JobInterface;
use Symfony\Contracts\EventDispatcher\Event;

class JobGeneratedEvent extends Event implements ManifestGeneratedEventInterface
{
    public const NAME = 'dealroadshow_k8s.manifest_generated.job';

    public function __construct(private JobInterface $manifest, private Job $job, private AppInterface $app)
    {
    }

    public function manifest(): JobInterface
    {
        return $this->manifest;
    }

    public function apiResource(): APIResourceInterface
    {
        return $this->job;
    }

    public function job(): Job
    {
        return $this->job;
    }

    public function app(): AppInterface
    {
        return $this->app;
    }
}
