<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Attribute\NoDefaultSelectorLabels;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Core\LabelSelector\SelectorConfigurator;
use Dealroadshow\K8S\Framework\Core\LabelsGeneratorInterface;
use Dealroadshow\K8S\Framework\Core\StatefulSet\StatefulSetInterface;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodEventInterface;

use Dealroadshow\K8S\Framework\Util\ClassName;

class DefaultSelectorLabelsSubscriber extends AbstractManifestMethodSubscriber
{
    public function __construct(private readonly LabelsGeneratorInterface $labelsGenerator)
    {
    }

    protected function supports(ProxyableMethodEventInterface $event): bool
    {
        $manifest = $event->proxyable();

        return ($manifest instanceof DeploymentInterface || $manifest instanceof StatefulSet)
            && 'selector' === $event->methodName();
    }

    protected function beforeMethod(ProxyableMethodEventInterface $event): void
    {
        /** @var DeploymentInterface|StatefulSetInterface $manifest */
        $manifest = $event->proxyable();
        if ($this->classHasNoSelectorAttribute(ClassName::real($manifest))) {
            return;
        }

        /** @var SelectorConfigurator $selector */
        $selector = $event->methodParams()['selector'];
        $selector->addLabels($this->labelsGenerator->byManifestInstance($manifest));
    }

    private function classHasNoSelectorAttribute(string $className): bool
    {
        $class = new \ReflectionClass($className);
        $attribute = AttributesUtil::fromClass($class, NoDefaultSelectorLabels::class);

        return null !== $attribute;
    }
}
