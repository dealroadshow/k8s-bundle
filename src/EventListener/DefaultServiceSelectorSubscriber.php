<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Attribute\NoDefaultServiceSelector;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Framework\Core\LabelsGeneratorInterface;
use Dealroadshow\K8S\Framework\Event\ServiceGeneratedEvent;
use Dealroadshow\K8S\Framework\Util\ClassName;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class DefaultServiceSelectorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LabelsGeneratorInterface $labelsGenerator,
        private array $excludedSelectorLabels,
    ) {
    }

    public function onServiceGenerated(ServiceGeneratedEvent $event): void
    {
        $manifest = $event->manifest();
        if ($this->classHasNoSelectorAttribute(ClassName::real($manifest))) {
            return;
        }
        $labels = $this->labelsGenerator->byManifestInstance($manifest);
        $labels = array_diff_key($labels, array_fill_keys($this->excludedSelectorLabels, null));
        $event->service()->spec()->selector()->addAll($labels);
    }

    public static function getSubscribedEvents(): array
    {
        return [ServiceGeneratedEvent::NAME => 'onServiceGenerated'];
    }

    private function classHasNoSelectorAttribute(string $className): bool
    {
        $class = new \ReflectionClass($className);
        $attribute = AttributesUtil::fromClass($class, NoDefaultServiceSelector::class);

        return null !== $attribute;
    }
}
