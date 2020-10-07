<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;
use RuntimeException;
use Throwable;

class ProjectGenerator
{
    private ClassDetailsResolver $resolver;

    public function __construct(ClassDetailsResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function generate(string $projectName): string
    {
        $details = $this->resolver->forProject($projectName);
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

        try {
            @mkdir($projectDir, 0777, true);
            @mkdir($appsDir, 0777, true);
        } catch (Throwable $err) {}

        $code = $this->generateCode($details, $projectName);
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $projectName): string
    {
        $templatesDir = dirname(__DIR__).'/../Resources/class-templates';

        ob_get_clean();
        ob_start();
        $variables = [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'projectName' => $projectName,
        ];
        extract($variables);
        require $templatesDir.DIRECTORY_SEPARATOR.'Project.tpl.php';

        return ob_get_clean();
    }
}
