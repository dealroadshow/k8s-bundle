<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Apps\StatefulSet;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;
use Dealroadshow\K8S\Data\PodTemplateSpec;

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
