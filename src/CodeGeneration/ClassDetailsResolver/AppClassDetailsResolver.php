<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\Util\Str;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use ReflectionObject;

class AppClassDetailsResolver
{
    private const SUFFIX_APP = 'App';
    private const APPS_DIR_NAME = 'Apps';

    private string $namespacePrefix;

    public function __construct(string $namespacePrefix)
    {
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function getClassDetails(ProjectInterface $project, string $appName): ClassDetails
    {
        $className = $this->className($appName);
        $namespace = $this->getNamespace($project, $appName);
        $dir = $this->getDir($project, $appName);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    private function className(string $appName): string
    {
        $className = Str::asClassName($appName);
        return Str::withSuffix($className,self::SUFFIX_APP);
    }

    private function getNamespace(ProjectInterface $project, string $appName): string
    {
        $rootNamespace = Str::asNamespace($project);
        $dirName = Str::asDirName($appName, self::SUFFIX_APP);

        return $rootNamespace.'\\'.self::APPS_DIR_NAME.'\\'.$dirName;
    }

    private function getDir(ProjectInterface $project, string $appName): string
    {
        $projectDir = Str::asDir($project);
        $dirName = Str::asDirName($appName, self::SUFFIX_APP);

        return $projectDir.DIRECTORY_SEPARATOR.self::APPS_DIR_NAME.DIRECTORY_SEPARATOR.$dirName;
    }
}