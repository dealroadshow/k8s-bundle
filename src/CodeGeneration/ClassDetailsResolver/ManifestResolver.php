<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\Util\Str;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use ReflectionObject;

class ManifestResolver
{
    private string $namespacePrefix;

    public function __construct(string $namespacePrefix)
    {
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function getClassDetails(AppInterface $app, string $depName, string $suffix): ClassDetails
    {
        $className = $this->getClassName($depName, $suffix);
        $namespace = $this->getNamespace($app, $depName, $suffix);
        $dir = $this->getDir($app, $depName, $suffix);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    private function getClassName(string $depName, string $suffix): string
    {
        $className = Str::asClassName($depName);

        return Str::withSuffix($className, $suffix);
    }

    private function getNamespace(AppInterface $app, string $depName, string $suffix): string
    {
        $dirName = Str::asDirName($depName, $suffix);
        $rootNamespace = Str::asNamespace($app);

        return $rootNamespace.'\\Manifests\\'.$dirName;
    }

    private function getDir(AppInterface $app, string $depName, string $suffix): string
    {
        $rootDir = Str::asDir($app);
        $dirName = Str::asDirName($depName, $suffix);

        return $rootDir.DIRECTORY_SEPARATOR.'Manifests'.DIRECTORY_SEPARATOR.$dirName;
    }
}
