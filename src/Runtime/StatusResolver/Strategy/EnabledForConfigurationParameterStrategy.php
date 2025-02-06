<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Runtime\StatusResolver\Strategy;

use Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute\EnabledForConfigurationParameter;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Runtime\ManifestStatusResolverStrategyInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class EnabledForConfigurationParameterStrategy implements ManifestStatusResolverStrategyInterface
{
    public function __construct(private ParameterBagInterface $parameters)
    {
    }

    public function isClassEnabled(\ReflectionClass $class): bool
    {
        /** @var EnabledForConfigurationParameter|null $attribute */
        $attribute = AttributesUtil::fromClassOrParents($class, EnabledForConfigurationParameter::class);
        if (null === $attribute) {
            return true;
        }

        if (!$this->parameters->has($attribute->parameter)) {
            throw new \RuntimeException(
                sprintf(
                    'Parameter "%s", used in PHP attribute "%s", does not exist in Symfony service container',
                    $attribute->parameter,
                    EnabledForConfigurationParameter::class
                )
            );
        }
        $parameter = $this->parameters->resolveValue($this->parameters->get($attribute->parameter));
        if (!is_bool($parameter)) {
            throw new \RuntimeException(
                sprintf(
                    'Parameter "%s", used in PHP attribute "%s", must contain a bool value, %s is given',
                    $attribute->parameter,
                    EnabledForConfigurationParameter::class,
                    gettype($parameter)
                )
            );
        }

        return $parameter;
    }
}
