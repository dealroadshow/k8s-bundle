<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum;

use Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter\PodTemplateGetter;
use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;

class AnnotationSetter
{
    public function __construct(private PodTemplateGetter $podTemplateGetter, private string $annotationDomain)
    {
    }

    public function setAnnotation(Deployment|Job|CronJob $workload, ChecksumAnnotation $annotation): void
    {
        $annotName = $this->annotationDomain.'/'.$annotation->name();
        $this->podTemplateGetter->get($workload)->metadata()->annotations()->add($annotName, $annotation->value());
    }
}