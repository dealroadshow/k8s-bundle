<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Dealroadshow\K8S\Framework\App\AppProcessor;
use Dealroadshow\K8S\Framework\Dumper\AppDumper;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;

class DumpAppCommand extends Command
{
    private const ARGUMENT_APP_ALIAS  = 'app_alias';
    private const ARGUMENT_OUTPUT_DIR = 'output_dir';

    protected static $defaultName = 'dealroadshow_k8s:dump:app';

    public function __construct(
        private AppRegistry $appRegistry,
        private AppProcessor $appProcessor,
        private AppDumper $dumper
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription('Processes app and dumps Yaml manifests to output dir.')
            ->addArgument(
                self::ARGUMENT_APP_ALIAS,
                InputArgument::REQUIRED,
                'Alias (name) of app to synthetize'
            )
            ->addArgument(
                self::ARGUMENT_OUTPUT_DIR,
                InputArgument::REQUIRED,
                'Directory where to save generated Yaml manifests'
            )
            ->setAliases([
                'k8s:dump:app',
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $appAlias = $input->getArgument(self::ARGUMENT_APP_ALIAS);
        try {
            if (!$this->appRegistry->has($appAlias)) {
                throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appAlias));
            }
            $outputDir = $this->getValidOutputDir($input);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $this->appProcessor->process($appAlias);
        $this->dumper->dump($appAlias, $outputDir);

        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $outputDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function getValidOutputDir(InputInterface $input): string
    {
        $outputDir = $input->getArgument(self::ARGUMENT_OUTPUT_DIR);
        $outputDir = realpath($outputDir);
        if (!file_exists(realpath($outputDir))) {
            throw new InvalidArgumentException(sprintf('Output dir "%s" does not exist', $outputDir));
        }
        $outputDir = realpath($outputDir);
        if (!is_dir($outputDir)) {
            throw new InvalidArgumentException(sprintf('Output path "%s" is not a directory', $outputDir));
        }

        return $outputDir;
    }
}
