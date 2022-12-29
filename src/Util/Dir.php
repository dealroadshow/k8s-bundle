<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Util;

class Dir
{
    public static function create(string $dir): void
    {
        try {
            @mkdir($dir, 0777, true);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf('Can\'t create directory "%s": %s', $dir, $e->getMessage()));
        }
    }
}
