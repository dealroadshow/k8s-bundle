<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\App\Integration;

use Dealroadshow\K8S\Framework\App\Integration\Localization\LocalizationStrategyInterface;

class DummyLocalizationStrategy implements LocalizationStrategyInterface
{
    public function localize(string $dependentAppAlias, array $dependencies): mixed
    {
        throw new \RuntimeException(sprintf('You must implement "%s" in your manifests application or to not use app configuration localization functionality.', LocalizationStrategyInterface::class));
    }
}
