<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Util\PropertyAccessUtil;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Core\StatefulSet\StatefulSetInterface;
use Dealroadshow\K8S\Framework\Event\ManifestMethodEvent;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodEventInterface;

class AutoSetReplicasSubscriber extends AbstractMethodSubscriber
{
    protected function supports(ProxyableMethodEventInterface $event): bool
    {
        $manifest = $event->proxyable();
        $supportedWorkload = $manifest instanceof DeploymentInterface || $manifest instanceof StatefulSetInterface;

        return $supportedWorkload && 'replicas' === $event->methodName();
    }

    protected function beforeMethod(ProxyableMethodEventInterface $event): void
    {
        /** @var DeploymentInterface|StatefulSetInterface $manifest */
        $manifest = $event->proxyable();
        /** @var AppInterface $app */
        $app = PropertyAccessUtil::getPropertyValue($manifest, 'app');
        $replicas = $app->manifestConfig($manifest::shortName())['replicas'] ?? null;
        if ($replicas) {
            $event->setReturnValue($replicas);
        }
    }

    protected static function eventNames(): iterable
    {
        yield ManifestMethodEvent::NAME;
    }
}
