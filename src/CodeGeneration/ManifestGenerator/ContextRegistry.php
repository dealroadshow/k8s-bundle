<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context\ContextInterface;

class ContextRegistry
{
    /**
     * @var array<string, ContextInterface>|ContextInterface[]|iterable
     */
    private array $contexts;

    /**
     * @param array<string, ContextInterface>|ContextInterface[]|iterable $contexts
     */
    public function __construct(iterable $contexts)
    {
        $this->contexts = [];
        foreach ($contexts as $name => $context) {
            $this->contexts[$context->kind()] = $context;
        }
    }

    public function has(string $kind): bool
    {
        return array_key_exists($kind, $this->contexts);
    }

    public function get(string $kind): ContextInterface
    {
        return $this->contexts[$kind];
    }

    /**
     * @return array<string, ContextInterface>|ContextInterface[]|iterable
     */
    public function all(): array
    {
        return $this->contexts;
    }

    public function kinds(): array
    {
        return array_keys($this->contexts);
    }
}
