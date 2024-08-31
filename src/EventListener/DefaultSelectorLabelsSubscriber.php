<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Attribute\NoDefaultSelectorLabels;
use Dealroadshow\Bundle\K8SBundle\Util\AttributesUtil;
use Dealroadshow\K8S\Api\Apps\V1\Deployment;
use Dealroadshow\K8S\Api\Apps\V1\StatefulSet;
use Dealroadshow\K8S\Framework\Core\LabelsGeneratorInterface;
use Dealroadshow\K8S\Framework\Event\DeploymentGeneratedEvent;
use Dealroadshow\K8S\Framework\Event\StatefulSetGeneratedEvent;
use Dealroadshow\K8S\Framework\Util\ClassName;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class DefaultSelectorLabelsSubscriber implements EventSubscriberInterface
{
    public function __construct(private LabelsGeneratorInterface $labelsGenerator)
    {
    }

    protected function onManifestGenerated(DeploymentGeneratedEvent|StatefulSetGeneratedEvent $event): void
    {
        $manifest = $event->manifest();
        if ($this->classHasNoSelectorAttribute(ClassName::real($manifest))) {
            return;
        }

        /** @var Deployment|StatefulSet $apiResource */
        $apiResource = $event->apiResource();
        $selector = $apiResource->spec()->selector()->matchLabels();
        $selector->addAll($this->labelsGenerator->byManifestInstance($manifest));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeploymentGeneratedEvent::NAME => 'onManifestGenerated',
            StatefulSetGeneratedEvent::NAME => 'onManifestGenerated',
        ];
    }

    private function classHasNoSelectorAttribute(string $className): bool
    {
        $class = new \ReflectionClass($className);
        $attribute = AttributesUtil::fromClass($class, NoDefaultSelectorLabels::class);

        return null !== $attribute;
    }
}
