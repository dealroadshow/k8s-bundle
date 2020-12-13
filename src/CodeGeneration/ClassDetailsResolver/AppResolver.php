<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\K8S\Framework\Util\Str;

class AppResolver
{
    private const SUFFIX_APP = 'App';

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

        return $this->namespacePrefix.'\\'.$dirName;
    }

    private function getDir(string $appName): string
    {
        $dirName = Str::asDirName($appName, self::SUFFIX_APP);

        return $this->codeDir.DIRECTORY_SEPARATOR.$dirName;
    }
}
