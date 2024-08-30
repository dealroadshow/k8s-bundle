<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\ContainerResources;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final readonly class ResourcesReferenceResolver
{
    private const string PATTERN = '/^(@([-\w]+)\.)?(\$([-\w]+))$/';

    /**
     * @param string $resourcesKey Either "requests" or "limits"
     */
    public function resolve(string $reference, string $resourcesKey, string $currentAppAlias, array $appsConfig): array
    {
        if (!preg_match(self::PATTERN, $reference, $matches)) {
            throw new InvalidConfigurationException(
                sprintf('Invalid resources reference string "%s"', $reference)
            );
        }

        $appAlias = $matches[2];
        $manifestShortName = $matches[4];
        $appAlias = $appAlias ?: $currentAppAlias;

        $appConfig = $appsConfig[$appAlias] ?? null;
        if (null === $appConfig) {
            throw new InvalidConfigurationException(
                sprintf('App "%s" from resources reference string "%s" does not exist', $appAlias, $reference)
            );
        }

        $manifestConfig = $appConfig['manifests'][$manifestShortName] ?? null;
        if (null === $manifestConfig) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Config for manifest "%s" in app "%s" from resources reference string "%s" does not exist',
                    $manifestShortName,
                    $appAlias,
                    $reference
                )
            );
        }

        $resourcesConfig = $manifestConfig['resources'][$resourcesKey] ?? null;
        if (null === $resourcesConfig) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Resources %s are not specified for manifest "%s" in app "%s" (resources reference string is "%s")',
                    $resourcesKey,
                    $manifestShortName,
                    $appAlias,
                    $reference
                )
            );
        }

        if (is_string($resourcesConfig)) {
            return $this->resolve($resourcesConfig, $resourcesKey, $appAlias, $appsConfig);
        } elseif (is_array($resourcesConfig)) {
            return $resourcesConfig;
        }

        throw new InvalidConfigurationException(
            sprintf(
                'Resources %s config for manifest "%s" in app "%s" is neither string nor array',
                $resourcesKey,
                $manifestShortName,
                $appAlias
            )
        );
    }
}
