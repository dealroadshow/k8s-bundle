<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\Service\ServiceInterface;

class ServiceContext extends AbstractContext
{
    public function kind(): string
    {
        return 'Service';
    }

    public static function interfaceName(): string
    {
        return ServiceInterface::class;
    }
}
