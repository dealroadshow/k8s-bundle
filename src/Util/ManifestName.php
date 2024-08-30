<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Util;

use Dealroadshow\K8S\Framework\Core\DynamicNameAwareInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;

final readonly class ManifestName
{
    public static function get(ManifestInterface $manifest): string
    {
        return $manifest instanceof DynamicNameAwareInterface
            ? $manifest->name()
            : $manifest::shortName();
    }
}
