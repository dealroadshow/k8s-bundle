<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\Util\EnvCheckSumCalculator;
use Dealroadshow\K8S\Framework\App\AppProcessor;
use Dealroadshow\K8S\Framework\Dumper\AppDumper;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class DumpAllCommand extends Command
{
    private const OPTION_RECREATE_DIR = 'recreate-output-dir';
    private const OPTION_PRINT_MANIFESTS = 'print';

    protected static $defaultName = 'dealroadshow_k8s:dump:all';

    public function __construct(
        private AppRegistry $registry,
        private AppProcessor $processor,
        private AppDumper $dumper,
        private string $manifestsDir,
        private EnvCheckSumCalculator $checkSumCalculator
    ) {
        $this->manifestsDir = rtrim($this->manifestsDir, '/');
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription('Dumps all apps and their manifests to Yaml files')
            ->addOption(
                self::OPTION_RECREATE_DIR,
                'R',
                InputOption::VALUE_NONE,
                'If specified, output directory will be deleted and recreated',
            )
            ->addOption(
                self::OPTION_PRINT_MANIFESTS,
                'P',
                InputOption::VALUE_NONE,
                'If specified, all manifests files will also be printed to stdout',
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $recreateOutputDir = $input->getOption(self::OPTION_RECREATE_DIR);
        if ($recreateOutputDir && file_exists($this->manifestsDir) && is_dir($this->manifestsDir)) {
            $fs = new Filesystem();
            $fs->remove([$this->manifestsDir]);
        }

        foreach ($this->registry->aliases() as $alias) {
            $this->processor->process($alias);
            $dir = $this->manifestsDir.DIRECTORY_SEPARATOR.$alias;
            $this->dumper->dump($alias, $dir);
        }
        $this->checkSumCalculator->calculateChecksums();

        $printManifests = $input->getOption(self::OPTION_PRINT_MANIFESTS);
        if ($printManifests) {
            $this->printManifests();
        }

        $io = $io->getErrorStyle();
        $io->success(sprintf('Manifests saved to directory "%s"', $this->manifestsDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function printManifests(): void
    {
        $filenames = array_merge(
            glob(sprintf('%s/*/**.yaml', $this->manifestsDir)),
            glob(sprintf('%s/*.yaml', $this->manifestsDir))
        );
        foreach ($filenames as $filename) {
            echo file_get_contents($filename), PHP_EOL, '---', PHP_EOL;
        }
    }
}
