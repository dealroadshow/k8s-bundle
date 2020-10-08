<?php


namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\Util\Str;

class ProjectClassDetailsResolver
{
    private const SUFFIX_PROJECT = 'Project';

    private string $projectsDir;
    private string $namespacePrefix;

    public function __construct(string $projectsDir, string $namespacePrefix)
    {
        $this->projectsDir = $projectsDir;
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function getClassDetails(string $projectName): ClassDetails
    {
        $className = $this->className($projectName);
        $namespace = $this->getNamespace($className);
        $dir = $this->getDir($className);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    private function className(string $projectName): string
    {
        $className = Str::asClassName($projectName);
        return Str::withSuffix($className,self::SUFFIX_PROJECT);
    }

    private function getNamespace(string $projectClassName): string
    {
        $classNameWithoutSuffix = Str::withoutSuffix($projectClassName, self::SUFFIX_PROJECT);

        return $this->namespacePrefix.'\\'.$classNameWithoutSuffix;
    }

    private function getDir(string $projectClass): string
    {
        return $this->projectsDir.DIRECTORY_SEPARATOR.Str::withoutSuffix($projectClass, self::SUFFIX_PROJECT);
    }
}