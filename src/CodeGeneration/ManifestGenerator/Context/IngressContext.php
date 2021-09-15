<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\Ingress\IngressInterface;

class IngressContext extends AbstractContext
{
    public function kind(): string
    {
        return 'Ingress';
    }

    public static function interfaceName(): string
    {
        return IngressInterface::class;
    }
}
