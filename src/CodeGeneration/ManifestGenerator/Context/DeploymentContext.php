<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;

class DeploymentContext extends AbstractContext
{
    public function kind(): string
    {
        return 'Deployment';
    }

    public static function interfaceName(): string
    {
        return DeploymentInterface::class;
    }
}
