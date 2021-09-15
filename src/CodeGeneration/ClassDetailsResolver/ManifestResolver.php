<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Util\Str;

class ManifestResolver
{
    public function __construct(private string $namespacePrefix)
    {
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    /**
     * @param bool $useDedicatedDir Whether to create a dir for this manifest (like dir "Example" for "ExampleDeployment")
     */
    public function getClassDetails(AppInterface $app, string $manifestName, string $suffix, bool $useDedicatedDir = false): ClassDetails
    {
        $className = Str::withSuffix(Str::asClassName($manifestName), $suffix);
        $namespace = Str::asNamespace($app).'\\Manifest';
        $dir = Str::asDir($app).DIRECTORY_SEPARATOR.'Manifest';
        $fileName = $className.'.php';

        if ($useDedicatedDir) {
            $dirName = Str::asDirName($manifestName, $suffix);
            $namespace .= '\\'.$dirName;
            $dir .= DIRECTORY_SEPARATOR.$dirName;
        }

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }
}
