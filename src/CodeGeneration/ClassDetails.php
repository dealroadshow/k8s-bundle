<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration;

readonly class ClassDetails
{
    private string $className;
    private string $namespace;
    private string $directory;
    private string $fileName;
    private string $filePath;

    public function __construct(string $className, string $namespace, string $directory, string $fileName)
    {
        $this->className = $className;
        $this->namespace = $namespace;
        $this->directory = $directory;
        $this->fileName = $fileName;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function directory(): string
    {
        return $this->directory;
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function fullFilePath(): string
    {
        return $this->directory.DIRECTORY_SEPARATOR.$this->fileName;
    }
}
