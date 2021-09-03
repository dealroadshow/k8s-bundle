<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\Event\ManifestsDumpedEvent;
use Dealroadshow\Bundle\K8SBundle\Event\ManifestsProcessedEvent;
use Dealroadshow\K8S\Framework\Core\ConfigMap\ConfigMapInterface;
use Dealroadshow\K8S\Framework\Core\Secret\SecretInterface;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Dealroadshow\K8S\Framework\App\AppProcessor;
use Dealroadshow\K8S\Framework\Dumper\AppDumper;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Symfony\Component\Filesystem\Filesystem;

class DumpAppCommand extends Command
{
    private const ARGUMENT_APPS_ALIASES  = 'apps-aliases';
    private const OPTION_OUTPUT_DIR      = 'output-dir';
    private const OPTION_PRINT_MANIFESTS = 'print';
    private const OPTION_RECREATE_DIR    = 'recreate-output-dir';
    private const OPTION_GENERATE_ENV_SOURCES = 'generate-all-env-sources';

    protected static $defaultName = 'dealroadshow_k8s:dump:app';

    public function __construct(
        private AppRegistry $appRegistry,
        private AppProcessor $appProcessor,
        private AppDumper $dumper,
        private EventDispatcherInterface $dispatcher,
        private string $manifestsDir
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription('Processes app and dumps Yaml manifests to output dir.')
            ->addArgument(
                self::ARGUMENT_APPS_ALIASES,
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Aliases (names) of apps to dump, separated by space'
            )
            ->addOption(
                self::OPTION_OUTPUT_DIR,
                'D',
                InputOption::VALUE_REQUIRED,
                'Directory where to save generated Yaml manifests'
            )
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
            ->addOption(
                self::OPTION_GENERATE_ENV_SOURCES,
                null,
                InputOption::VALUE_NONE,
                'If specified, ALL ConfigMaps and Secrets from all apps (not only specified) will be generated',
            )
            ->setAliases([
                'k8s:dump:app',
                'k8s:dump:apps',
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $activeAppsAliases = $input->getArgument(self::ARGUMENT_APPS_ALIASES);
        try {
            $outputDir = $this->getValidOutputDir($input);
            foreach ($activeAppsAliases as $appAlias) {
                if (!$this->appRegistry->has($appAlias)) {
                    throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appAlias));
                }
            }
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $recreateOutputDir = $input->getOption(self::OPTION_RECREATE_DIR);
        if ($recreateOutputDir && file_exists($outputDir) && is_dir($outputDir)) {
            $fs = new Filesystem();
            $fs->remove([$outputDir]);
        }

        if ($input->getOption(self::OPTION_GENERATE_ENV_SOURCES)) {
            foreach (array_diff($this->appRegistry->aliases(), $activeAppsAliases) as $appAlias) {
                $this->appProcessor->processInstancesOf(
                    $appAlias,
                    [SecretInterface::class, ConfigMapInterface::class]
                );
            }
        }

        foreach ($activeAppsAliases as $appAlias) {
            $this->appProcessor->process($appAlias);
        }
        $this->dispatcher->dispatch(new ManifestsProcessedEvent(), ManifestsProcessedEvent::NAME);

        foreach ($this->appRegistry->aliases() as $appAlias) {
            $this->dumper->dump($appAlias, $outputDir.DIRECTORY_SEPARATOR.$appAlias);
        }
        $this->dispatcher->dispatch(new ManifestsDumpedEvent(), ManifestsDumpedEvent::NAME);

        $printManifests = $input->getOption(self::OPTION_PRINT_MANIFESTS);
        if ($printManifests) {
            $this->printManifests($outputDir);
        }

        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $outputDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function getValidOutputDir(InputInterface $input): string
    {
        $outputDir = $input->getOption(self::OPTION_OUTPUT_DIR) ?? $this->manifestsDir;
        if (null === $outputDir) {
            throw new InvalidArgumentException('Option "--output-dir" must be specified');
        }
        if (!file_exists(realpath($outputDir))) {
            throw new InvalidArgumentException(sprintf('Output dir "%s" does not exist', $outputDir));
        }
        $outputDir = realpath($outputDir);
        if (!is_dir($outputDir)) {
            throw new InvalidArgumentException(sprintf('Output path "%s" is not a directory', $outputDir));
        }

        return $outputDir;
    }

    private function printManifests(string $outputDir): void
    {
        $filenames = array_merge(
            glob(sprintf('%s/*/**.yaml', $outputDir)),
            glob(sprintf('%s/*.yaml', $outputDir))
        );
        foreach ($filenames as $filename) {
            echo file_get_contents($filename), PHP_EOL, '---', PHP_EOL;
        }
    }
}
