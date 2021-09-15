<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\Job\JobInterface;

class JobContext extends AbstractContext
{
    public function kind(): string
    {
        return 'Job';
    }

    public static function interfaceName(): string
    {
        return JobInterface::class;
    }
}
