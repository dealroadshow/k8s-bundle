<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait ClearCacheTrait
{
    private function clearCache(): void
    {
        $command = $this->getApplication()->find('cache:clear');
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $returnCode = $command->execute($input, $output);

        if (self::SUCCESS !== $returnCode) {
            throw new RuntimeException(
                sprintf(
                    'Command was not able to clear cache: "cache:clear" failed: %s',
                    $output->fetch()
                )
            );
        }
    }
}
