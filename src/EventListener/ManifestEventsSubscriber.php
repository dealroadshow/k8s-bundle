<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EventListener;

use Dealroadshow\Bundle\K8SBundle\Event\ConfigMapGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\CronJobGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\DeploymentGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\IngressGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\JobGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestMethodCalledEvent;
use Dealroadshow\Bundle\K8SBundle\Event\SecretGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\ServiceGeneratedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\StatefulSetGeneratedEvent;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Core\ConfigMap\ConfigMapInterface;
use Dealroadshow\K8S\Framework\Core\CronJob\CronJobInterface;
use Dealroadshow\K8S\Framework\Core\Deployment\DeploymentInterface;
use Dealroadshow\K8S\Framework\Core\Ingress\IngressInterface;
use Dealroadshow\K8S\Framework\Core\Job\JobInterface;
use Dealroadshow\K8S\Framework\Core\Secret\SecretInterface;
use Dealroadshow\K8S\Framework\Core\Service\ServiceInterface;
use Dealroadshow\K8S\Framework\Core\StatefulSet\StatefulSetInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ManifestEventsSubscriber implements EventSubscriberInterface
{
    public function onMethodCalled(ManifestMethodCalledEvent $event, string $eventName, EventDispatcherInterface|EventDispatcher $dispatcher): void
    {
        $manifest = $event->manifest();
        $methodName = $event->methodName();
        $params = $event->methodParams();
        /** @var AppInterface $app */
        $app = (fn () => $this->{'app'})->call($manifest);

        if ('configureDeployment' === $methodName && $manifest instanceof DeploymentInterface) {
            $event = new DeploymentGeneratedEvent($manifest, $params['deployment'], $app);
        } elseif ('configureService' === $methodName && $manifest instanceof ServiceInterface) {
            $event = new ServiceGeneratedEvent($manifest, $params['service'], $app);
        } elseif ('configureIngress' === $methodName && $manifest instanceof IngressInterface) {
            $event = new IngressGeneratedEvent($manifest, $params['ingress'], $app);
        } elseif ('configureCronJob' === $methodName && $manifest instanceof CronJobInterface) {
            $event = new CronJobGeneratedEvent($manifest, $params['cronJob'], $app);
        } elseif ('configureJob' === $methodName && $manifest instanceof JobInterface) {
            $event = new JobGeneratedEvent($manifest, $params['job'], $app);
        } elseif ('configureConfigMap' === $methodName && $manifest instanceof ConfigMapInterface) {
            $event = new ConfigMapGeneratedEvent($manifest, $params['configMap'], $app);
        } elseif ('configureSecret' === $methodName && $manifest instanceof SecretInterface) {
            $event = new SecretGeneratedEvent($manifest, $params['secret'], $app);
        } elseif ('configureStatefulSet' === $methodName && $manifest instanceof StatefulSetInterface) {
            $event = new StatefulSetGeneratedEvent($manifest, $params['statefulSet'], $app);
        } else {
            return;
        }

        $dispatcher->dispatch($event, $event::NAME);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ManifestMethodCalledEvent::NAME => ['onMethodCalled', -1024],
        ];
    }
}
