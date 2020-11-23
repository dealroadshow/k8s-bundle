<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait ClearCacheTrait
{
    private function clearCache(): void
    {
        $command = $this->getApplication()->find('cache:clear');
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
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
