<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum\Calculator;

use Dealroadshow\Bundle\K8SBundle\Checksum\ChecksumAnnotation;
use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;

interface ChecksumCalculatorInterface
{
    public function calculate(Deployment|Job|CronJob $workload): ChecksumAnnotation;
}