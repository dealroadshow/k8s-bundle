<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Attribute;

/**
 * This attribute is used on workload manifests like Deployments and Jobs
 * to specify, that default selector labels should not be applied to them.
 * @see \Dealroadshow\Bundle\K8SBundle\EventListener\DefaultSelectorLabelsSubscriber
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class NoDefaultSelectorLabels
{
}
