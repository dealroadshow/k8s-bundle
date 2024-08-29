<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum;

use Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter\PodTemplateGetter;
use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Batch\V1\CronJob;
use Dealroadshow\K8S\Api\Batch\V1\Job;

class AnnotationSetter
{
    public function __construct(private PodTemplateGetter $podTemplateGetter, private string $annotationDomain)
    {
    }

    public function setAnnotation(Deployment|Job|CronJob|StatefulSet $workload, ChecksumAnnotation $annotation): void
    {
        $annotName = $this->annotationDomain.'/'.$annotation->name();
        $this->podTemplateGetter->get($workload)->metadata()->annotations()->add($annotName, $annotation->value());
    }
}
