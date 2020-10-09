<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\AppClassDetailsResolver;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\TemplateRender;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Registry\ProjectRegistry;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class AppGenerator
{
    private ProjectRegistry $projectRegistry;
    private AppRegistry $appRegistry;
    private AppClassDetailsResolver $resolver;
    /**
     * @var TemplateRender
     */
    private TemplateRender $render;

    public function __construct(ProjectRegistry $projectRegistry, AppRegistry $appRegistry, AppClassDetailsResolver $resolver, TemplateRender $render)
    {
        $this->projectRegistry = $projectRegistry;
        $this->appRegistry = $appRegistry;
        $this->resolver = $resolver;
        $this->render = $render;
    }

    public function generate(string $projectName, string $appName): string
    {
        $this->ensureAppNameIsValid($appName);
        $project = $this->getProject($projectName);
        $details = $this->resolver->getClassDetails($project, $appName);
        $appDir = $details->directory();

        Dir::create($appDir);
        Dir::create($appDir.DIRECTORY_SEPARATOR.'Manifests');

        $code = $this->generateCode($details, $appName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $appName): string
    {
        return $this->render->render('App.tpl.php', [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'appName' => $appName,
        ]);
    }

    private function getProject(string $projectName): ProjectInterface
    {
        return $this->projectRegistry->get($projectName);
    }

    private function ensureAppNameIsValid(string $appName): void
    {
        if ($this->appRegistry->has($appName)) {
            throw new InvalidArgumentException(
                sprintf('App "%s" already exists', $appName)
            );
        }
    }
}
