<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\CronJob\CronJobInterface;

class CronJobContext extends AbstractContext
{
    public function kind(): string
    {
        return 'CronJob';
    }

    public static function interfaceName(): string
    {
        return CronJobInterface::class;
    }
}
