<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\APIResourceInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;

interface ManifestGeneratedEventInterface
{
    public function manifest(): ManifestInterface;
    public function apiResource(): APIResourceInterface;
}
