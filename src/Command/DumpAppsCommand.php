<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\ManifestGenerator\ManifestsGenerationService;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

#[AsCommand(
    name: 'dealroadshow_k8s:dump:apps',
    description: 'Dumps manifests for specifies apps to specified directory',
    aliases: ['k8s:dump:apps', 'k8s:dump:app']
)]
class DumpAppsCommand extends Command
{
    public const ARGUMENT_APPS_ALIASES = 'apps-aliases';
    public const OPTION_OUTPUT_DIR = 'output-dir';
    public const OPTION_RECREATE_DIR = 'recreate-output-dir';

    protected static $defaultName = 'dealroadshow_k8s:dump:app';

    public function __construct(private ManifestsGenerationService $generationService, private string $manifestsDir)
    {
        parent::__construct();
    }

    public function configure(): void
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputDir = $this->getValidOutputDir($input, (bool)$input->getOption(self::OPTION_RECREATE_DIR));
        $aliases = $input->getArgument(self::ARGUMENT_APPS_ALIASES);

        $this->generationService->processApps(...$aliases);
        $this->generationService->dumpApps(...$aliases);

        $io = $io->getErrorStyle();
        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $outputDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function getValidOutputDir(InputInterface $input, bool $recreateDir): string
    {
        $outputDir = $input->getOption(self::OPTION_OUTPUT_DIR) ?? $this->manifestsDir;
        if (null === $outputDir) {
            throw new InvalidArgumentException('Option "--output-dir" must be specified');
        }
        if (!file_exists($outputDir)) {
            try {
                mkdir($outputDir);
            } catch (Throwable $error) {
                throw new RuntimeException(sprintf('Cannot create output dir "%s": %s', $outputDir, $error->getMessage()));
            }
        }
        $realpath = realpath($outputDir);
        if (!$realpath || is_dir($realpath)) {
            throw new InvalidArgumentException(sprintf('Output path "%s" is not a directory', $outputDir));
        }
        $outputDir = $realpath;

        if ($recreateDir) {
            $fs = new Filesystem();
            $fs->remove($outputDir);
            $fs->mkdir($outputDir);
        }

        return $realpath;
    }
}
