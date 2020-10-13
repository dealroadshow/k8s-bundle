<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetailsResolver\ManifestResolver;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context\ContextInterface;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\TemplateRenderer;
use Dealroadshow\Bundle\K8SBundle\Util\Dir;
use Dealroadshow\K8S\Framework\App\AppInterface;

class ManifestGenerator
{
    private ManifestResolver $resolver;
    private TemplateRenderer $renderer;

    public function __construct(ManifestResolver $resolver, TemplateRenderer $renderer)
    {
        $this->resolver = $resolver;
        $this->renderer = $renderer;
    }

    public function generate(string $manifestName, ContextInterface $context, AppInterface $app): string
    {
        $details = $this->resolver->getClassDetails(
            $app,
            $manifestName,
            $context->kind(),
            $context->createDedicatedDir()
        );
        Dir::create($details->directory());
        $code = $this->generateCode($details, $manifestName, $context);
        $fileName = $details->fullFilePath();
        file_put_contents($fileName, $code);

        return $fileName;
    }

    protected function generateCode(ClassDetails $details, string $jobName, ContextInterface $context): string
    {
        return $this->renderer->render(
            $context->templateName(),
            $context->templateVariables($details, $jobName)
        );
    }
}
