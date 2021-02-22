<?php

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\K8S\Framework\Util\ReflectionUtil;
use Dealroadshow\K8S\Framework\Util\Str;
use LogicException;
use ReflectionObject;

abstract class AbstractEnvAwareMethodSubscriber extends AbstractMethodResultSubscriber
{
    abstract protected function methodName(): string;

    public function __construct(protected string $env)
    {
    }

    protected function afterMethod(ManifestMethodCalledEvent $event): void
    {
        $class = new ReflectionObject($event->manifest());
        $methodName = $event->methodName();
        if ($event->methodName() !== $methodName || !$class->hasMethod($methodName)) {
            return;
        }
        if (!$this->supports($event)) {
            return;
        }
        $envAwareMethodName = $this->methodName().Str::asClassName($this->env);
        if (!$class->hasMethod($envAwareMethodName)) {
            return;
        }

        $originalMethod = $class->getMethod($methodName);
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

        $returned = $envAwareMethod->invoke($event->manifest(), ...$event->methodParams());
        if ($this->replacesReturnValue()
            && $envAwareMethod->hasReturnType()
            && 'void' !== $envAwareMethod->getReturnType()
            && null !== $returned
        ) {
            $event->setReturnValue($returned);
        }
    }

    protected function replacesReturnValue(): bool
    {
        return true;
    }

    protected function supports(ManifestMethodCalledEvent $event): bool
    {
        return $event->methodName() === $this->methodName();
    }
}
