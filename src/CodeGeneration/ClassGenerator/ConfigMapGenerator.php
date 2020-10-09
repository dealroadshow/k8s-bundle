<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\ManifestClassDetailsResolver;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ConfigMap\ConfigMapInterface;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ConfigMapGenerator
{
    private ManifestClassDetailsResolver $resolver;
    private ManifestRegistry $manifestRegistry;
    private AppRegistry $appRegistry;

    public function __construct(AppRegistry $appRegistry, ManifestRegistry $manifestRegistry, ManifestClassDetailsResolver $resolver)
    {
        $this->resolver = $resolver;
        $this->manifestRegistry = $manifestRegistry;
        $this->appRegistry = $appRegistry;
    }

    public function generate(string $appName, string $depName): string
    {
        $app = $this->getApp($appName);
        $this->ensureConfigMapNameIsValid($app, $depName);
        $details = $this->resolver->getClassDetails($app, $depName, 'ConfigMap');
        $this->createDirs($details);
        $code = $this->generateCode($details, $depName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $configMapName): string
    {
        $templatesDir = dirname(__DIR__).'/../Resources/class-templates';

        ob_get_clean();
        ob_start();
        $variables = [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'configMapName' => $configMapName,
            'fileName' => $configMapName,
        ];
        extract($variables);
        require $templatesDir.DIRECTORY_SEPARATOR.'ConfigMap.tpl.php';

        return ob_get_clean();
    }

    private function createDirs(ClassDetails $details): void
    {
        $depDir = $details->directory();
        try {
            @mkdir($depDir, 0777, true);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Cannot generate ConfigMap directory "%s": %s', $depDir, $e->getMessage())
            );
        }
    }

    private function getApp(string $appName): AppInterface
    {
        if (!$this->appRegistry->has($appName)) {
            throw new InvalidArgumentException(
                sprintf('App "%s" does not exist', $appName)
            );
        }

        return $this->appRegistry->get($appName);
    }

    private function ensureConfigMapNameIsValid(AppInterface $app, string $depName): void
    {
        $manifests = $this->manifestRegistry->byApp($app, ConfigMapInterface::class);
        foreach($manifests as $manifest) {
            if($manifest::name() === $depName) {
                throw new InvalidArgumentException(
                    sprintf('ConfigMap "%s" already exists', $depName)
                );
            }
        }
    }
}
