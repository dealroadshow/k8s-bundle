<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Attribute\NoDefaultMetadataLabels;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\LabelsGeneratorInterface;
use Dealroadshow\K8S\Framework\Event\ManifestGeneratedEvent;
use Dealroadshow\K8S\Framework\Util\ClassName;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class DefaultMetadataLabelsSubscriber implements EventSubscriberInterface
{
    public function __construct(private LabelsGeneratorInterface $labelsGenerator)
    {
    }

    public function onManifestGenerated(ManifestGeneratedEvent $event): void
    {
        $manifest = $event->manifest();
        if ($this->classHasNoSelectorAttribute(ClassName::real($manifest))) {
            return;
        }
        $labels = $this->labelsGenerator->byManifestInstance($manifest);
        $event->apiResource()->metadata()->labels()->addAll($labels);
    }

    public static function getSubscribedEvents(): array
    {
        return [ManifestGeneratedEvent::NAME => 'onManifestGenerated'];
    }

    private function classHasNoSelectorAttribute(string $className): bool
    {
        $class = new \ReflectionClass($className);
        $attribute = AttributesUtil::fromClass($class, NoDefaultMetadataLabels::class);

        return null !== $attribute;
    }
}
