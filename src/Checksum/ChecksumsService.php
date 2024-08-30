<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum;

use Dealroadshow\Bundle\K8SBundle\Checksum\Calculator\ChecksumCalculatorInterface;
use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Batch\V1\CronJob;
use Dealroadshow\K8S\Api\Batch\V1\Job;

class ChecksumsService
{
    /**
     * @var ChecksumCalculatorInterface[]
     */
    private iterable $calculators;

    public function __construct(private readonly AnnotationSetter $annotationSetter, iterable $calculators)
    {
        $this->calculators = $calculators;
    }

    public function calculateChecksums(Deployment|Job|CronJob|StatefulSet $workload): void
    {
        foreach ($this->calculators as $calculator) {
            $annotation = $calculator->calculate($workload);
            $this->annotationSetter->setAnnotation($workload, $annotation);
        }
    }
}
