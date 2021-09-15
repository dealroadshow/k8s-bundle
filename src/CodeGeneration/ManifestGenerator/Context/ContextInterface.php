<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;

interface ContextInterface
{
    public function createDedicatedDir(): bool;

    public function kind(): string;

    public function parentInterface(): string;

    public function templateName(): string;

    public function templateVariables(ClassDetails $details, string $manifestName): array;
}
