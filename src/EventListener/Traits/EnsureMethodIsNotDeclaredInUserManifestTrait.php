<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener\Traits;

use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ProxyableInterface;
use Dealroadshow\Proximity\ProxyInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait EnsureMethodIsNotDeclaredInUserManifestTrait
{
    private string $env;

    protected function ensureMethodIsNotDeclaredInUserManifest(ProxyableInterface $manifest, string $methodName, AppInterface $app): void
    {
        $class = new \ReflectionObject($manifest);
        if ($class->implementsInterface(ProxyInterface::class)) {
            $class = $class->getParentClass();
        }

        $basicMethod = $class->getMethod($methodName);
        $envSpecificMethodName = $methodName.ucfirst($this->env);
        $envSpecificMethod = $class->hasMethod($envSpecificMethodName)
            ? $class->getMethod($envSpecificMethodName)
            : null;

        foreach ([$basicMethod, $envSpecificMethod] as $method) {
            /** @var \ReflectionMethod $method */
            if ($method?->getDeclaringClass() !== $class) {
                continue;
            }

            throw new \LogicException(
                sprintf(
                    '%s for manifest "%s" in app "%s" are declared both in manifests config and in method %s::%s(), '
                    .'which leads to ambiguity. Please choose one way to define %s for this manifest.',
                    ucfirst($methodName),
                    $class->getShortName(),
                    $app->alias(),
                    $class->getShortName(),
                    $method->getName(),
                    $methodName
                )
            );
        }
    }

    #[Required]
    public function setCurrentEnv(string $env): void
    {
        $this->env = $env;
    }
}
