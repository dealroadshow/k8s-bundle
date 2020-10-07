<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver;
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
    private ClassDetailsResolver $resolver;

    public function __construct(ProjectRegistry $projectRegistry, AppRegistry $appRegistry, ClassDetailsResolver $resolver)
    {
        $this->projectRegistry = $projectRegistry;
        $this->appRegistry = $appRegistry;
        $this->resolver = $resolver;
    }

    public function generate(string $projectName, string $appName): string
    {
        $this->ensureAppNameIsValid($appName);
        $project = $this->getProject($projectName);
        $details = $this->resolver->forApp($project, $appName);
        $this->createAppDirs($details);
        $code = $this->generateCode($details, $appName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $appName): string
    {
        $templatesDir = dirname(__DIR__).'/../Resources/class-templates';

        ob_get_clean();
        ob_start();
        $variables = [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'appName' => $appName,
        ];
        extract($variables);
        require $templatesDir.DIRECTORY_SEPARATOR.'App.tpl.php';

        return ob_get_clean();
    }

    private function createAppDirs(ClassDetails $details): void
    {
        $appDir = $details->directory();
        try {
            @mkdir($appDir, 0777, true);
            @mkdir($appDir.DIRECTORY_SEPARATOR.'Manifests');
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Cannot generate App directory "%s": %s', $appDir, $e->getMessage())
            );
        }
    }

    private function getProject(string $projectName): ProjectInterface
    {
        if (!$this->projectRegistry->has($projectName)) {
            throw new InvalidArgumentException(
                sprintf('Project "%s" does not exist', $projectName)
            );
        }

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
