<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\Traits;

use Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler\AppsPass;

trait GetAppAliasTrait
{
    private function getAppAlias(string $serviceId, array $tags): string
    {
        $alias = null;
        foreach ($tags as $tag) {
            if (array_key_exists('alias', $tag)) {
                $alias = $tag['alias'];
                break;
            }
        }
        if (null === $alias) {
            throw new \LogicException(sprintf('"%s" tag on app service "%s" does not have "alias" attribute.', AppsPass::APP_TAG, $serviceId));
        }

        return $alias;
    }
}
