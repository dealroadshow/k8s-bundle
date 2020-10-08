<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\Util\Str;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use ReflectionObject;

class DeploymentClassDetailsResolver
{
    private const SUFFIX = 'Deployment';

    private string $namespacePrefix;

    public function __construct(string $namespacePrefix)
    {
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function getClassDetails(AppInterface $app, string $depName): ClassDetails
    {
        $className = $this->className($depName);
        $namespace = $this->getNamespace($app, $depName);
        $dir = $this->getDir($app, $depName);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    private function className(string $depName): string
    {
        $className = Str::asClassName($depName);
        return Str::withSuffix($className, self::SUFFIX);
    }

    private function getNamespace(AppInterface $app, string $depName): string
    {
        $dirName = Str::asDirName($depName, self::SUFFIX);
        $rootNamespace = Str::asNamespace($app);
        return $rootNamespace.'\\Manifests\\'.$dirName;
    }

    private function getDir(AppInterface $app, string $depName): string
    {
        $rootDir = Str::asDir($app);
        $dirName = Str::asDirName($depName, self::SUFFIX);
        return $rootDir.DIRECTORY_SEPARATOR.'Manifests'.DIRECTORY_SEPARATOR.$dirName;
    }
}