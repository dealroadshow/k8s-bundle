<?php

namespace Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassDetails;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use LogicException;
use ReflectionClass;
use ReflectionException;

abstract class AbstractContext implements ContextInterface
{
    abstract protected static function interfaceName(): string;

    public function createDedicatedDir(): bool
    {
        return true;
    }

    public function parentInterface(): string
    {
        $interfaceName = static::interfaceName();

        try {
            $reflection = new ReflectionClass($interfaceName);
        } catch (ReflectionException $e) {
            throw new LogicException(
                sprintf(
                    'Class "%s", returned by %s::interfaceName(), does not exist',
                    $interfaceName,
                    static::class
                )
            );
        }

        if (!$reflection->isInterface()) {
            throw new LogicException(
                sprintf(
                    'Class "%s", returned by %s::interfaceName(), must be an interface',
                    $interfaceName,
                    static::class
                )
            );
        }

        if (!$reflection->implementsInterface(ManifestInterface::class)) {
            throw new LogicException(
                sprintf(
                    'Interface "%s", returned by %s::interfaceName(), must extend %s',
                    $interfaceName,
                    static::class,
                    ManifestInterface::class
                )
            );
        }

        return $interfaceName;
    }

    public function templateName(): string
    {
        return $this->kind().'.tpl.php';
    }

    public function templateVariables(ClassDetails $details, string $manifestName): array
    {
        return [
            'namespace' => $details->namespace(),
            'className' => $details->className(),
            'manifestName' => $manifestName,
            'fileName' => $manifestName,
        ];
    }
}
