<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\Util\Str;

class AppResolver
{
    private const SUFFIX_APP = 'App';
    private const APPS_DIR_NAME = 'Apps';

    public function __construct(private string $codeDir, private string $namespacePrefix)
    {
        $this->namespacePrefix = trim($namespacePrefix, '\\');
    }

    public function getClassDetails(string $appName): ClassDetails
    {
        $className = $this->getClassName($appName);
        $namespace = $this->getNamespace($appName);
        $dir = $this->getDir($appName);
        $fileName = $className.'.php';

        return new ClassDetails($className, $namespace, $dir, $fileName);
    }

    private function getClassName(string $appName): string
    {
        $className = Str::asClassName($appName);
        return Str::withSuffix($className,self::SUFFIX_APP);
    }

    private function getNamespace(string $appName): string
    {
        $dirName = Str::asDirName($appName, self::SUFFIX_APP);

        return $this->namespacePrefix.'\\'.self::APPS_DIR_NAME.'\\'.$dirName;
    }

    private function getDir(string $appName): string
    {
        $dirName = Str::asDirName($appName, self::SUFFIX_APP);
        $ds = DIRECTORY_SEPARATOR;

        return $this->codeDir.$ds.self::APPS_DIR_NAME.$ds.$dirName;
    }
}
