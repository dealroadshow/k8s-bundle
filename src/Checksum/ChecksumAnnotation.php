<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum;

class ChecksumAnnotation
{
    public function __construct(private string $name, private string $value)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }
}
