<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\Secret\SecretInterface;

class SecretContext extends AbstractContext
{
    public function kind(): string
    {
        return 'Secret';
    }

    public static function interfaceName(): string
    {
        return SecretInterface::class;
    }
}
