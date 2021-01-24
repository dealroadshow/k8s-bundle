<?php

namespace Dealroadshow\Bundle\K8SBundle\Middleware;

use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Middleware\ManifestMethodMiddlewareInterface;

abstract class AbstractManifestMiddleware implements ManifestMethodMiddlewareInterface
{
    public function beforeMethodCall(ManifestInterface $manifest, string $methodName, array $params, &$returnValue)
    {
    }

    public function afterMethodCall(ManifestInterface $manifest, string $methodName, array $params, mixed $returnedValue, mixed &$returnValue)
    {
    }

    public static function priority(): int
    {
        return 0;
    }
}
