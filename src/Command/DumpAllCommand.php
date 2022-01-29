<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\ManifestGenerator\ManifestsGenerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dealroadshow_k8s:dump:all',
    description: 'Dumps all manifests to specified directory',
    aliases: ['k8s:dump:all']
)]
class DumpAllCommand extends Command
{
    public const OPTION_RECREATE_DIR = 'recreate-output-dir';

    public function __construct(private ManifestsGenerationService $generationService, private string $manifestsDir)
    {
        $this->manifestsDir = rtrim($this->manifestsDir, '/');

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_RECREATE_DIR,
                'R',
                InputOption::VALUE_NONE,
                'If specified, output directory will be deleted and recreated',
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $recreateOutputDir = $input->getOption(self::OPTION_RECREATE_DIR);
        if ($recreateOutputDir && file_exists($this->manifestsDir) && is_dir($this->manifestsDir)) {
            rmdir($this->manifestsDir);
            mkdir($this->manifestsDir);
        }

        $this->generationService->processAll();
        $this->generationService->dumpAll($this->manifestsDir);

        $io = $io->getErrorStyle();
        $io->success(sprintf('Manifests saved to directory "%s"', $this->manifestsDir));
        $io->newLine();

        return self::SUCCESS;
    }
}
