<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Apps\StatefulSet;
use Dealroadshow\K8S\API\Batch\Job;
use Dealroadshow\K8S\Data\PodTemplateSpec;

class DefaultPodTemplateGetter
{
    public function get(Deployment|Job|StatefulSet $workload): PodTemplateSpec
    {
        return $workload->spec()->template();
    }
}
