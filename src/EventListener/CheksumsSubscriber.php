<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Checksum\ChecksumsService;
use Dealroadshow\Bundle\K8SBundle\Registry\APIResourceRegistry;
use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Batch\V1\CronJob;
use Dealroadshow\K8S\Api\Batch\V1\Job;
use Dealroadshow\K8S\Framework\Event\ManifestsProcessedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheksumsSubscriber implements EventSubscriberInterface
{
    private const WORKLOAD_KINDS = [
        Deployment::KIND,
        Job::KIND,
        CronJob::KIND,
        StatefulSet::KIND,
    ];

    public function __construct(private ChecksumsService $service, private APIResourceRegistry $registry)
    {
    }

    public function onManifestsProcessed(): void
    {
        foreach ($this->registry->all() as $kind => $apiResource) {
            if (!in_array($kind, self::WORKLOAD_KINDS, true)) {
                continue;
            }

            $this->service->calculateChecksums($apiResource);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManifestsProcessedEvent::NAME => ['onManifestsProcessed', -1024],
        ];
    }
}
