<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use InvalidArgumentException;
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

        try {
            $returnCode = $command->execute($input, $output);
        } catch (InvalidArgumentException $e) {
            // Some bug in Symfony when 'cache:clear' command is called programmatically
            if ('The "no-warmup" option does not exist.' !== $e->getMessage()) {
                throw $e;
            }
            $returnCode = 0;
        }

        if (self::SUCCESS !== $returnCode) {
            throw new RuntimeException(sprintf('Command was not able to clear cache: "cache:clear" failed: %s', $output->fetch()));
        }
    }
}
