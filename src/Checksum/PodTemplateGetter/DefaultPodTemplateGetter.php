<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Batch\V1\Job;
use Dealroadshow\K8S\Api\Core\V1\PodTemplateSpec;

class DefaultPodTemplateGetter
{
    public function get(Deployment|Job|StatefulSet $workload): PodTemplateSpec
    {
        return $workload->spec()->template();
    }
}
