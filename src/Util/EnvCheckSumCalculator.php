<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

use Dealroadshow\K8S\API\Apps\Deployment;
use Dealroadshow\K8S\API\Batch\CronJob;
use Dealroadshow\K8S\API\Batch\Job;
use Dealroadshow\K8S\API\ConfigMap;
use Dealroadshow\K8S\API\Secret;
use Dealroadshow\K8S\Framework\Renderer\YamlRenderer;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Yaml\Yaml;

class EnvCheckSumCalculator
{
    private const WORKLOAD_KINDS = [
        Deployment::KIND,
        Job::KIND,
        CronJob::KIND,
    ];

    public function __construct(private YamlRenderer $renderer, private string $manifestsDir, private string $annotationDomain)
    {
    }

    public function calculateChecksums(): void
    {
        $filenames = glob(sprintf('%s/*/**.yaml', $this->manifestsDir));

        $nameToManifestMap = [];
        $nameToFilenameMap = [];
        foreach ($filenames as $filename) {
            $yaml = file_get_contents($filename);
            $data = Yaml::parse($yaml);
            $name = $data['metadata']['name'];
            $nameToManifestMap[$name] = $data;
            $nameToFilenameMap[$name] = $filename;
        }

        foreach ($nameToManifestMap as $name => $data) {
            if (!in_array($data['kind'], self::WORKLOAD_KINDS)) {
                continue;
            }
            $containers = $this->retrieveContainers($data);
            $envSources = $this->envSources($containers);
            $checkSum = $this->checksum($envSources, $nameToManifestMap);
            $this->setAnnotation($data, $checkSum);
            $yaml = $this->renderer->render($data);
            $filename = $nameToFilenameMap[$name];

            file_put_contents($filename, $yaml);
        }
    }

    private function setAnnotation(array &$manifestData, string $checksum): void
    {
        if (in_array($manifestData['kind'], [Deployment::KIND, Job::KIND])) {
            $template = &$manifestData['spec']['template'];
        } elseif (CronJob::KIND === $manifestData['kind']) {
            $template = &$manifestData['spec']['jobTemplate']['spec']['template'];
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported manifest with kind "%s": cannot retrieve "template" section',
                    $manifestData['kind']
                )
            );
        }

        $annotationName = sprintf(
            '%s/env-sources-checksum',
            rtrim($this->annotationDomain, '/')
        );
        $template['metadata']['annotations'][$annotationName] = $checksum;
    }

    private function retrieveContainers(array $manifestData): array
    {
        return match($manifestData['kind']) {
            Deployment::KIND, Job::KIND => $manifestData['spec']['template']['spec']['containers'],
            CronJob::KIND => $manifestData['spec']['jobTemplate']['spec']['template']['spec']['containers'],
            default => throw new InvalidArgumentException(
                sprintf(
                    'Unsupported manifest with kind "%s": cannot retrieve "containers" section',
                    $manifestData['kind']
                )
            )
        };
    }

    private function checksum(array $envSources, array $manifests): string
    {
        $sourcesData = [];
        foreach ($envSources as $source) {
            if (!array_key_exists($source, $manifests)) {
                throw new LogicException(
                    'One of containers uses "%s" as an env source, but no manifest with such name was found',
                    $source
                );
            }

            $manifestData = $manifests[$source];
            if (!in_array($manifestData['kind'], [Secret::KIND, ConfigMap::KIND])) {
                throw new LogicException(
                    'One of containers uses "%s" as an env source, and corresponding manifest must be a ConfigMap or Secret, but is of kind "%s"',
                    $source,
                    $manifestData['kind']
                );
            }

            $this->ksortRecursive($manifestData);
            $sourcesData[$source] = json_encode($manifestData);
        }

        $sourcesDataString = implode(PHP_EOL, $sourcesData);

        return md5($sourcesDataString);
    }

    private function envSources(array $containers): array
    {
        $envSources = [];
        foreach ($containers as $container) {
            $envSources += $this->containerEnvSources($container);
        }
        $envSources = array_keys($envSources);
        sort($envSources, SORT_STRING);

        return $envSources;
    }

    private function containerEnvSources(array $container): array
    {
        $envSources = [];

        $env = $container['env'] ?? [];
        foreach ($env as $var) {
            if (!array_key_exists('valueFrom', $var)) {
                continue;
            }
            $valueFrom = $var['valueFrom'];
            if (array_key_exists('configMapKeyRef', $valueFrom)) {
                $key = 'configMapKeyRef';
            } elseif (array_key_exists('secretKeyRef', $valueFrom)) {
                $key = 'secretKeyRef';
            } else {
                continue;
            }

            $sourceName = $valueFrom[$key]['name'];

            // we use keys instead of values for algorithmic efectiveness
            $envSources[$sourceName] = null;
        }

        $envFrom = $container['envFrom'] ?? [];
        foreach ($envFrom as $envSource) {
            if (array_key_exists('configMapRef', $envSource)) {
                $key = 'configMapRef';
            } elseif (array_key_exists('secretRef', $envSource)) {
                $key = 'secretRef';
            } else {
                continue;
            }

            $sourceName = $envSource[$key]['name'];
            $envSources[$sourceName] = null;
        }

        return $envSources;
    }

    private function ksortRecursive(array &$array): void
    {
        ksort($array);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->ksortRecursive($array[$key]);
            }
        }
    }
}
