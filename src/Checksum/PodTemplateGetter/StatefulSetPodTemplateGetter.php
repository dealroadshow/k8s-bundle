<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter;

use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Core\V1\PodTemplateSpec;

class StatefulSetPodTemplateGetter
{
    public function get(StatefulSet $workload): PodTemplateSpec
    {
        return $workload->getSpec()->template();
    }
}
