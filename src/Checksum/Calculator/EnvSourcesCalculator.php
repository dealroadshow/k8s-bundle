<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\Calculator;

use Dealroadshow\Bundle\K8SBundle\Checksum\ChecksumAnnotation;
use Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter\PodTemplateGetter;
use Dealroadshow\Bundle\K8SBundle\Registry\APIResourceRegistry;
use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Apps\StatefulSet;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;
use Dealroadshow\K8S\API\ConfigMap;
use Dealroadshow\K8S\API\Secret;
use Dealroadshow\K8S\Data\Container;
use Dealroadshow\K8S\Framework\Renderer\JsonRenderer;
use LogicException;

class EnvSourcesCalculator implements ChecksumCalculatorInterface
{
    use ChecksumTrait;

    private const ANNOTATION_NAME = 'env-sources-checksum';

    public function __construct(
        private PodTemplateGetter $podTemplateGetter,
        private APIResourceRegistry $registry,
        private JsonRenderer $renderer,
    ) {
    }

    public function calculate(Job|CronJob|Deployment|StatefulSet $workload): ChecksumAnnotation
    {
        $containers = $this->podTemplateGetter->get($workload)->spec()->containers();

        $envSources = [];
        foreach ($containers->all() as $container) {
            $envSources += $this->envVarSources($container);
            $envSources += $this->envFromSources($container);
        }

        return new ChecksumAnnotation(self::ANNOTATION_NAME, $this->checksum($envSources));
    }

    private function envFromSources(Container $container): array
    {
        $sources = [];
        foreach ($container->envFrom()->all() as $envSource) {
            if (($configMapRef = $envSource->configMapRef()) && $name = $configMapRef->getName()) {
                $kind = ConfigMap::KIND;
            } elseif (($secretRef = $envSource->secretRef()) && $name = $secretRef->getName()) {
                $kind = Secret::KIND;
            } else {
                throw new LogicException('EnvFromSource instance must contain either "configMapRef" or "secretRef" field');
            }

            $source = $this->getSource($name, $kind);
            $sources[spl_object_hash($source)] = $source;
        }

        return $sources;
    }

    private function envVarSources(Container $container): array
    {
        $sources = [];
        foreach ($container->env()->all() as $envVar) {
            $envVarSource = $envVar->valueFrom();
            if ($configMapRef = $envVarSource->getConfigMapKeyRef()) {
                $name = $configMapRef->getName();
                $kind = ConfigMap::KIND;
            } elseif ($secretRef = $envVarSource->getSecretKeyRef()) {
                $name = $secretRef->getName();
                $kind = Secret::KIND;
            } else {
                continue;
            }

            $source = $this->getSource($name, $kind);
            $sources[spl_object_hash($source)] = $source;
        }

        return $sources;
    }

    private function getSource(string $name, string $kind): ConfigMap|Secret
    {
        if (!$this->registry->has($name, $kind)) {
            throw new LogicException(sprintf('One of manifests uses "%s" as an env source, but no %s with such name was found', $name, $kind));
        }

        return $this->registry->get($name, $kind);
    }
}
