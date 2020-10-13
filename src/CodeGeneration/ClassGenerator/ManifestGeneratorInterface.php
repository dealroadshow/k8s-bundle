<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

interface ManifestGeneratorInterface
{
    public function generate(string $appName, string $manifestName): string;
}