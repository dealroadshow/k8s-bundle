<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\ProjectClassDetailsResolver;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\TemplateRender;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use RuntimeException;
use Throwable;

class ProjectGenerator
{
    private ProjectClassDetailsResolver $resolver;
    /**
     * @var TemplateRender
     */
    private TemplateRender $render;

    public function __construct(ProjectClassDetailsResolver $resolver, TemplateRender $render)
    {
        $this->resolver = $resolver;
        $this->render = $render;
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
        return $this->render->render('Project.tpl.php', [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'projectName' => $projectName,
        ]);
    }
}
