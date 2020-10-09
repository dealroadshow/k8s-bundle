<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration;

use InvalidArgumentException;

class TemplateRenderer
{
    private string $templatesDir;

    public function __construct(string $templatesDir)
    {
        $this->templatesDir = $templatesDir;
    }

    public function render(string $templateName, array $variables)
    {
        ob_get_clean();
        ob_start();
        extract($variables);
        $tplPath = $this->templatesDir.DIRECTORY_SEPARATOR.$templateName;
        if(!file_exists($tplPath)) {
            throw new InvalidArgumentException(
                sprintf('Template "%s" does not exists', $templateName)
            );
        }
        require $tplPath;

        return ob_get_clean();
    }
}
