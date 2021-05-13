<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum\Calculator;

use Dealroadshow\Bundle\K8SBundle\Checksum\ChecksumAnnotation;
use Dealroadshow\Bundle\K8SBundle\Checksum\PodTemplateGetter\PodTemplateGetter;
use Dealroadshow\Bundle\K8SBundle\Registry\APIResourceRegistry;
use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;
use Dealroadshow\K8S\API\ConfigMap;
use Dealroadshow\K8S\API\Secret;
use LogicException;

class VolumesCalculator implements ChecksumCalculatorInterface
{
    use ChecksumTrait;

    private const ANNOTATION_NAME = 'volume-sources-checksum';

    public function __construct(private PodTemplateGetter $podTemplateGetter, private APIResourceRegistry $registry)
    {
    }

    public function calculate(Job|CronJob|Deployment $workload): ChecksumAnnotation
    {
        $volumes = $this->podTemplateGetter->get($workload)->spec()->volumes();

        $volumeSources = [];
        foreach ($volumes->all() as $volume) {
            if ($configMapRef = $volume->configMap()) {
                $name = $configMapRef->getName();
                $kind = ConfigMap::KIND;
            } elseif ($secretRef = $volume->secret()) {
                $name = $secretRef->getSecretName();
                $kind = Secret::KIND;
            } else {
                continue;
            }

            $source = $this->getSource($name, $kind);
            $volumeSources[spl_object_hash($source)] = $source;
        }

        return new ChecksumAnnotation(self::ANNOTATION_NAME, $this->checksum($volumeSources));
    }

    private function getSource(string $name, string $kind): ConfigMap|Secret
    {
        if (!$this->registry->has($name, $kind)) {
            throw new LogicException(
                sprintf(
                    'One of manifests uses "%s" as a volume source, but no %s with such name was found',
                    $name,
                    $kind
                )
            );
        }

        return $this->registry->get($name, $kind);
    }
}
