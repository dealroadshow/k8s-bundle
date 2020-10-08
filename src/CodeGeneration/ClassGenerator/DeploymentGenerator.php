<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\DeploymentClassDetailsResolver;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use Dealroadshow\K8S\Framework\Registry\ProjectRegistry;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class DeploymentGenerator
{
    private ProjectRegistry $projectRegistry;
    private DeploymentClassDetailsResolver $resolver;
    /**
     * @var ManifestRegistry
     */
    private ManifestRegistry $manifestRegistry;

    public function __construct(ProjectRegistry $projectRegistry, ManifestRegistry $manifestRegistry, DeploymentClassDetailsResolver $resolver)
    {
        $this->projectRegistry = $projectRegistry;
        $this->resolver = $resolver;
        $this->manifestRegistry = $manifestRegistry;
    }

    public function generate(string $projectName, string $appName, string $depName): string
    {
        $project = $this->getProject($projectName);
        $app = $this->getApp($project, $appName);
        $this->ensureDeploymentNameIsValid($app, $depName);
        $details = $this->resolver->getClassDetails($app, $depName);
        $this->createDirs($details);
        $code = $this->generateCode($details, $depName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $deploymentName): string
    {
        $templatesDir = dirname(__DIR__).'/../Resources/class-templates';

        ob_get_clean();
        ob_start();
        $variables = [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'deploymentName' => $deploymentName,
            'fileName' => $deploymentName,
        ];
        extract($variables);
        require $templatesDir.DIRECTORY_SEPARATOR.'Deployment.tpl.php';

        return ob_get_clean();
    }

    private function createDirs(ClassDetails $details): void
    {
        $depDir = $details->directory();
        try {
            @mkdir($depDir, 0777, true);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Cannot generate Deployment directory "%s": %s', $depDir, $e->getMessage())
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

    private function getApp(ProjectInterface $project, string $appName): AppInterface
    {
        foreach ($project->apps() as $app) {
            if($app->name() === $appName) {
                return $app;
            }
        }
        throw new InvalidArgumentException(
            sprintf('Project "%s" does not have App "%s"', $project->name(), $appName)
        );
    }

    private function ensureDeploymentNameIsValid(AppInterface $app, string $depName): void
    {
        $manifests = $this->manifestRegistry->byApp($app);
        foreach($manifests as $manifest) {
            if($manifest instanceof DeploymentInterface && $manifest::name() === $depName) {
                throw new InvalidArgumentException(
                    sprintf('Deployment "%s" already exists', $depName)
                );
            }
        }
    }
}
