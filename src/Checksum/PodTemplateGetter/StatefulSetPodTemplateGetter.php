<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\API\Apps\StatefulSet;
use Dealroadshow\K8S\Data\PodTemplateSpec;

class StatefulSetPodTemplateGetter
{
    public function get(StatefulSet $workload): PodTemplateSpec
    {
        return $workload->getSpec()->template();
    }
}
