<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\Framework\Core\ManifestInterface;

class ManifestMethodCalledEvent extends ManifestMethodEvent
{
    const NAME = 'dealroadshow_k8s.manifest.method_called';

    public function __construct(ManifestInterface $proxy, ManifestInterface $manifest, string $methodName, array $methodParams, private mixed $returnedValue)
    {
        parent::__construct($proxy, $manifest, $methodName, $methodParams);
    }

    public function returnedValue(): mixed
    {
        return $this->returnedValue;
    }
}
