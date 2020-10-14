<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\ProjectResolver;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use RuntimeException;

class ProjectGenerator
{
    private ProjectResolver $resolver;
    private TemplateRenderer $renderer;

    public function __construct(ProjectResolver $resolver, TemplateRenderer $renderer)
    {
        $this->resolver = $resolver;
        $this->renderer = $renderer;
    }

    public function generate(string $projectName): string
    {
        $details = $this->resolver->getClassDetails($projectName);
        $className = $details->className();
        $namespace = $details->namespace();
        $projectDir = $details->directory();
        $appsDir = $projectDir.DIRECTORY_SEPARATOR.'Apps';
        $fileName = $details->fullFilePath();

        if (file_exists($fileName)) {
            throw new RuntimeException(
                sprintf(
                    'Cannot generate project class "%s", since file "%s" already exists',
                    $namespace.'\\'.$className,
                    $fileName
                )
            );
        }

        Dir::create($projectDir);
        Dir::create($appsDir);

        $code = $this->generateCode($details, $projectName);
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $projectName): string
    {
        return $this->renderer->render('Project.tpl.php', [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'projectName' => $projectName,
        ]);
    }
}
