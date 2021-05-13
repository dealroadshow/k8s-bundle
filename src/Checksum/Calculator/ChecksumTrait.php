<?php

namespace Dealroadshow\Bundle\K8SBundle\Checksum\Calculator;

use Dealroadshow\Bundle\K8SBundle\Util\SortUtil;
use Dealroadshow\K8S\API\ConfigMap;
use Dealroadshow\K8S\API\Secret;

trait ChecksumTrait
{
    /**
     * @param Secret[]|ConfigMap[] $sources
     *
     * @return string
     */
    private function checksum(array $sources): string
    {
        $mappedByNameAndKind = [];
        foreach ($sources as $source) {
            $data = json_decode(json_encode($source), true);
            SortUtil::ksortRecursive($data);
            $json = json_encode($data);

            $key = sprintf('%s:%s', $source->metadata()->getName(), $source::KIND);
            $mappedByNameAndKind[$key] = $json;
        }

        ksort($mappedByNameAndKind);

        return md5(implode(PHP_EOL, $mappedByNameAndKind));
    }
}