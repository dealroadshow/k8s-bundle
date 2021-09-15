<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum;

use Dealroadshow\Bundle\K8SBundle\Checksum\Calculator\ChecksumCalculatorInterface;
use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Apps\StatefulSet;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;

class ChecksumsService
{
    /**
     * @var ChecksumCalculatorInterface[]
     */
    private iterable $calculators;

    public function __construct(private AnnotationSetter $annotationSetter, iterable $calculators)
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
