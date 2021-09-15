<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Checksum\Calculator;

use Dealroadshow\Bundle\K8SBundle\Util\SortUtil;
use Dealroadshow\K8S\API\ConfigMap;
use Dealroadshow\K8S\API\Secret;

trait ChecksumTrait
{
    /**
     * @param Secret[]|ConfigMap[] $sources
     */
    private function checksum(array $sources): string
    {
        $mappedByNameAndKind = [];
        foreach ($sources as $source) {
            $data = $this->renderer->renderAsArray($source);
            SortUtil::ksortRecursive($data);
            $json = json_encode($data);

            $key = sprintf('%s:%s', $source->metadata()->getName(), $source::KIND);
            $mappedByNameAndKind[$key] = $json;
        }

        ksort($mappedByNameAndKind);

        return md5(implode(PHP_EOL, $mappedByNameAndKind));
    }
}
