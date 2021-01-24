<?php

namespace Dealroadshow\Bundle\K8SBundle\Middleware;

use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use Dealroadshow\K8S\Framework\Util\Str;
use LogicException;
use ReflectionException;
use ReflectionObject;

abstract class AbstractEnvAwareMethodMiddleware extends AbstractManifestMiddleware
{
    abstract protected function methodName(): string;
    abstract protected function returnsValue(): bool;
    abstract protected function allowNullReturnValue(): bool;

    public function __construct(private string $env)
    {
    }

    /**
     * @param ManifestInterface $manifest
     * @param string            $methodName
     * @param array             $params
     * @param mixed             $returnedValue
     * @param mixed             $returnValue
     *
     * @throws ReflectionException
     */
    public function afterMethodCall(ManifestInterface $manifest, string $methodName, array $params, mixed $returnedValue, mixed &$returnValue)
    {
        $class = new ReflectionObject($manifest);
        $envAwareMethodName = $this->methodName().Str::asClassName($this->env);
        if (!$class->hasMethod($envAwareMethodName)) {
            return;
        }

        $originalMethod = $class->getMethod($this->methodName());
        $envAwareMethod = $class->getMethod($envAwareMethodName);
        if (!ReflectionUtil::sameSignature($originalMethod, $envAwareMethod)) {
            throw new LogicException(
                sprintf(
                    'Class "%s" has env-aware version of method "%s": method "%s", but signatures does not match.',
                    $class->getName(),
                    $methodName,
                    $envAwareMethodName
                )
            );
        }

        $returned = $envAwareMethod->invoke($manifest, ...$params);
        if ($this->returnsValue()) {
            if (null !== $returned || $this->allowNullReturnValue()) {
                $returnValue = $returned;
            }
        }
    }
}
