<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\ManifestResolver;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\TemplateRenderer;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use InvalidArgumentException;

class DeploymentGenerator implements ManifestGeneratorInterface
{
    private ManifestResolver $resolver;
    private ManifestRegistry $manifestRegistry;
    private AppRegistry $appRegistry;
    private TemplateRenderer $renderer;

    public function __construct(AppRegistry $appRegistry, ManifestRegistry $manifestRegistry, ManifestResolver $resolver, TemplateRenderer $renderer)
    {
        $this->resolver = $resolver;
        $this->manifestRegistry = $manifestRegistry;
        $this->appRegistry = $appRegistry;
        $this->renderer = $renderer;
    }

    public function generate(string $appName, string $deploymentName): string
    {
        $app = $this->getApp($appName);
        $this->ensureDeploymentNameIsValid($app, $deploymentName);
        $details = $this->resolver->getClassDetails($app, $deploymentName, 'Deployment');
        Dir::create($details->directory());
        $code = $this->generateCode($details, $deploymentName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $deploymentName): string
    {
        return $this->renderer->render('Deployment.tpl.php', [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'deploymentName' => $deploymentName,
            'fileName' => $deploymentName,
        ]);
    }

    private function getApp(string $appName): AppInterface
    {
        return $this->appRegistry->get($appName);
    }

    private function ensureDeploymentNameIsValid(AppInterface $app, string $depName): void
    {
        $manifests = $this->manifestRegistry->byApp($app, DeploymentInterface::class);
        foreach($manifests as $manifest) {
            if($manifest::name() === $depName) {
                throw new InvalidArgumentException(
                    sprintf('Deployment "%s" already exists', $depName)
                );
            }
        }
    }
}
