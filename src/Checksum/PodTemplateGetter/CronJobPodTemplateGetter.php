<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\Api\Batch\V1\CronJob;
use Dealroadshow\K8S\Api\Core\V1\PodTemplateSpec;

class CronJobPodTemplateGetter
{
    public function get(CronJob $workload): PodTemplateSpec
    {
        return $workload->getSpec()->jobTemplate()->spec()->template();
    }
}
