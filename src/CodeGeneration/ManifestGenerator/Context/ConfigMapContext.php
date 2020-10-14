<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\ConfigMap\ConfigMapInterface;

class ConfigMapContext extends AbstractContext
{
    public function kind(): string
    {
        return 'ConfigMap';
    }

    public static function interfaceName(): string
    {
        return ConfigMapInterface::class;
    }
}
