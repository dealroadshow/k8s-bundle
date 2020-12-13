<?php

namespace Dealroadshow\Bundle\K8SBundle\Util;

use RuntimeException;
use Throwable;

class Dir
{
    public static function create(string $dir): void
    {
        try {
            @mkdir($dir, 0777, true);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Can\'t create directory "%s": %s', $dir, $e->getMessage())
            );
        }
    }
}
