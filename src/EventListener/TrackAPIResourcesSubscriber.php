<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Registry\APIResourceRegistry;
use Dealroadshow\K8S\Framework\Event\ConfigMapGeneratedEvent;
use Dealroadshow\K8S\Framework\Event\CronJobGeneratedEvent;
use Dealroadshow\K8S\Framework\Event\DeploymentGeneratedEvent;
use Dealroadshow\K8S\Framework\Event\JobGeneratedEvent;
use Dealroadshow\K8S\Framework\Event\ManifestGeneratedEventInterface;
use Dealroadshow\K8S\Framework\Event\SecretGeneratedEvent;
use Dealroadshow\K8S\Framework\Event\StatefulSetGeneratedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrackAPIResourcesSubscriber implements EventSubscriberInterface
{
    private const EVENTS = [
        ConfigMapGeneratedEvent::NAME,
        CronJobGeneratedEvent::NAME,
        DeploymentGeneratedEvent::NAME,
        JobGeneratedEvent::NAME,
        SecretGeneratedEvent::NAME,
        StatefulSetGeneratedEvent::NAME,
    ];

    public function __construct(private APIResourceRegistry $registry)
    {
    }

    public function onManifestGenerated(ManifestGeneratedEventInterface $event): void
    {
        $this->registry->add($event->apiResource());
    }

    public static function getSubscribedEvents(): array
    {
        return array_fill_keys(self::EVENTS, ['onManifestGenerated', -1024]);
    }
}
