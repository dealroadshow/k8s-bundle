<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Batch\V1\CronJob;
use Dealroadshow\K8S\Api\Batch\V1\Job;
use Dealroadshow\K8S\Api\Core\V1\PodTemplateSpec;

class PodTemplateGetter
{
    private array $kindsMap;

    public function __construct(
        DefaultPodTemplateGetter $defaultGetter,
        CronJobPodTemplateGetter $cronJobGetter,
        StatefulSetPodTemplateGetter $stsGetter
    ) {
        $this->kindsMap = [
            Deployment::KIND => $defaultGetter,
            Job::KIND => $defaultGetter,
            CronJob::KIND => $cronJobGetter,
            StatefulSet::KIND => $stsGetter,
        ];
    }

    public function get(Deployment|Job|CronJob|StatefulSet $workload): PodTemplateSpec
    {
        return $this->kindsMap[$workload::KIND]->get($workload);
    }
}
