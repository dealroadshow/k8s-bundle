<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\Calculator;

use Dealroadshow\Bundle\K8SBundle\Checksum\ChecksumAnnotation;
use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Api\Batch\V1\CronJob;
use Dealroadshow\K8S\Api\Batch\V1\Job;

interface ChecksumCalculatorInterface
{
    public function calculate(Deployment|Job|CronJob|StatefulSet $workload): ChecksumAnnotation;
}
