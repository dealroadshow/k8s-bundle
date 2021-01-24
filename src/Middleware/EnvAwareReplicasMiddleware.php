<?php

namespace Dealroadshow\Bundle\K8SBundle\Middleware;

use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;

class EnvAwareReplicasMiddleware extends AbstractEnvAwareMethodMiddleware
{
    public function supports(ManifestInterface $manifest, string $methodName, array $params): bool
    {
        return $manifest instanceof DeploymentInterface && 'replicas' === $methodName;
    }

    protected function methodName(): string
    {
        return 'replicas';
    }

    protected function returnsValue(): bool
    {
        return true;
    }

    protected function allowNullReturnValue(): bool
    {
        return false;
    }
}
