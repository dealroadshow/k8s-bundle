<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\AppResolver;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;

class AppGenerator
{
    public function __construct(private AppRegistry $appRegistry, private AppResolver $resolver, private TemplateRenderer $renderer)
    {
    }

    public function generate(string $appName): string
    {
        $this->ensureAppNameIsValid($appName);
        $details = $this->resolver->getClassDetails($appName);
        $appDir = $details->directory();

        Dir::create($appDir);
        Dir::create($appDir.DIRECTORY_SEPARATOR.'Manifest');
        Dir::create($appDir.DIRECTORY_SEPARATOR.'Container');
        Dir::create($appDir.DIRECTORY_SEPARATOR.'Resources');

        $code = $this->generateCode($details, $appName);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    private function generateCode(ClassDetails $details, string $appName): string
    {
        return $this->renderer->render('App.tpl.php', [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'appName' => $appName,
        ]);
    }

    private function ensureAppNameIsValid(string $appName): void
    {
        if ($this->appRegistry->has($appName)) {
            throw new \InvalidArgumentException(sprintf('App "%s" already exists', $appName));
        }
    }
}
