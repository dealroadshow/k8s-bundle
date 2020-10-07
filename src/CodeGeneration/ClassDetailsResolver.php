<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration;

use Dealroadshow\Bundle\K8SBundle\Util\Str;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use ReflectionObject;

class ClassDetailsResolver
{
    private const SUFFIX_PROJECT = 'Project';
    private const SUFFIX_APP = 'App';
    private const APPS_DIR_NAME = 'Apps';

    private string $projectsDir;
    private string $namespacePrefix;

    public function __construct(string $projectsDir, string $namespacePrefix)
    {
        $this->projectsDir = $projectsDir;
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function forProject(string $projectName): ClassDetails
    {
        $className = $this->className($projectName, self::SUFFIX_PROJECT);
        $namespace = $this->projectNamespace($className);
        $dir = $this->projectDir($className);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    public function forApp(ProjectInterface $project, string $appName): ClassDetails
    {
        $className = $this->className($appName, self::SUFFIX_APP);
        $namespace = $this->appNamespace($project, $appName);
        $dir = $this->appDir($project, $appName);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    private function className(string $projectName, string $suffix): string
    {
        $className = Str::asClassName($projectName);
        $classNameWithoutSuffix = Str::withoutSuffix($className, $suffix);
        $className = $classNameWithoutSuffix.$suffix;

        return $className;
    }

    private function appNamespace(ProjectInterface $project, string $appName): string
    {
        $reflection = new ReflectionObject($project);
        $namespace = $reflection->getNamespaceName();
        $namespace = trim($namespace, '\\');
        $appDirName = $this->appDirName($appName);

        return $namespace.'\\'.self::APPS_DIR_NAME.'\\'.$appDirName;
    }

    private function projectNamespace(string $projectClassName): string
    {
        $classNameWithoutSuffix = Str::withoutSuffix($projectClassName, self::SUFFIX_PROJECT);

        return $this->namespacePrefix.'\\'.$classNameWithoutSuffix;
    }

    private function appDir(ProjectInterface $project, string $appName): string
    {
        $reflection = new ReflectionObject($project);
        $projectDir = dirname($reflection->getFileName());
        $appDirName = $this->appDirName($appName);

        return $projectDir.DIRECTORY_SEPARATOR.self::APPS_DIR_NAME.DIRECTORY_SEPARATOR.$appDirName;
    }

    private function projectDir(string $projectClass): string
    {
        return $this->projectsDir.DIRECTORY_SEPARATOR.Str::withoutSuffix($projectClass, self::SUFFIX_PROJECT);
    }

    private function appDirName(string $appName): string
    {
        return Str::withoutSuffix(Str::asClassName($appName), self::SUFFIX_APP);
    }
}