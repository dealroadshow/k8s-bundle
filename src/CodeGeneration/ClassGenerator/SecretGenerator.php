<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\ManifestClassDetailsResolver;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\TemplateRender;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Secret\SecretInterface;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SecretGenerator
{
    private ManifestClassDetailsResolver $resolver;
    private ManifestRegistry $manifestRegistry;
    private AppRegistry $appRegistry;
    /**
     * @var TemplateRender
     */
    private TemplateRender $render;

    public function __construct(AppRegistry $appRegistry, ManifestRegistry $manifestRegistry, ManifestClassDetailsResolver $resolver, TemplateRender $render)
    {
        $this->resolver = $resolver;
        $this->manifestRegistry = $manifestRegistry;
        $this->appRegistry = $appRegistry;
        $this->render = $render;
    }

    public function generate(string $appName, string $depName): string
    {
        $app = $this->getApp($appName);
        $this->ensureSecretNameIsValid($app, $depName);
        $details = $this->resolver->getClassDetails($app, $depName, 'Secret');
        Dir::create($details->directory());
        $code = $this->generateCode($details, $depName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $secretName): string
    {
        return $this->render->render('Secret.tpl.php', [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'secretName' => $secretName,
            'fileName' => $secretName,
        ]);
    }

    private function getApp(string $appName): AppInterface
    {
        return $this->appRegistry->get($appName);
    }

    private function ensureSecretNameIsValid(AppInterface $app, string $depName): void
    {
        $manifests = $this->manifestRegistry->byApp($app, SecretInterface::class);
        foreach($manifests as $manifest) {
            if($manifest::name() === $depName) {
                throw new InvalidArgumentException(
                    sprintf('Secret "%s" already exists', $depName)
                );
            }
        }
    }
}
