<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ConfigMapGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\CronJobGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\DeploymentGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\JobGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestGeneratedEventInterface;
use Dealroadshow\Bundle\K8SBundle\Event\SecretGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\StatefulSetGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Registry\APIResourceRegistry;
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
