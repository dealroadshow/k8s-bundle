<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\Data\PodTemplateSpec;

class CronJobPodTemplateGetter
{
    public function get(CronJob $workload): PodTemplateSpec
    {
        return $workload->getSpec()->jobTemplate()->spec()->template();
    }
}
