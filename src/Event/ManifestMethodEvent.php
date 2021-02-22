<?php

namespace Dealroadshow\Bundle\K8SBundle\Event;

use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ManifestMethodEvent extends Event
{
    const NAME = 'dealroadshow_k8s.manifest.before_method';

    private mixed $returnValue = null;

    public function __construct(private ManifestInterface $proxy, private ManifestInterface $manifest, private string $methodName, private array $methodParams)
    {
    }

    public function proxy(): ManifestInterface
    {
        return $this->proxy;
    }

    public function manifest(): ManifestInterface
    {
        return $this->manifest;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function methodParams(): array
    {
        return $this->methodParams;
    }

    public function getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    public function setReturnValue(mixed $returnValue): void
    {
        $this->returnValue = $returnValue;
    }
}
