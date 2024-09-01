<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Attribute\NoDefaultMetadataLabels;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\LabelsGeneratorInterface;
use Dealroadshow\K8S\Framework\Core\ManifestInterface;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Event\ProxyableMethodEventInterface;
use Dealroadshow\K8S\Framework\Util\ClassName;

class DefaultMetadataLabelsSubscriber extends AbstractManifestMethodSubscriber
{
    public function __construct(private readonly LabelsGeneratorInterface $labelsGenerator)
    {
    }

    protected function supports(ProxyableMethodEventInterface $event): bool
    {
        return 'metadata' === $event->methodName()
            && ($event->methodParams()['meta'] ?? null) instanceof MetadataConfigurator;
    }

    protected function beforeMethod(ProxyableMethodEventInterface $event): void
    {
        /** @var ManifestInterface $manifest */
        $manifest = $event->proxyable();
        if ($this->classHasNoLabelsAttribute(ClassName::real($manifest))) {
            return;
        }
        $labels = $this->labelsGenerator->byManifestInstance($manifest);
        /** @var MetadataConfigurator $meta */
        $meta = $event->methodParams()['meta'];
        $meta->labels()->addAll($labels);
    }

    private function classHasNoLabelsAttribute(string $className): bool
    {
        $class = new \ReflectionClass($className);
        $attribute = AttributesUtil::fromClass($class, NoDefaultMetadataLabels::class);

        return null !== $attribute;
    }
}
