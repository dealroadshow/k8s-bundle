<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\ManifestGenerator\ManifestsGenerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dealroadshow_k8s:print:apps',
    description: 'Prints manifests for specified apps to stdout',
    aliases: ['k8s:print:apps', 'k8s:print:app']
)]
class PrintAppsCommand extends Command
{
    public const ARGUMENT_APPS_ALIASES = 'apps-aliases';

    public function __construct(private readonly ManifestsGenerationService $generationService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_APPS_ALIASES,
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Aliases (names) of apps, separated by space'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $aliases = $input->getArgument(self::ARGUMENT_APPS_ALIASES);

        $this->generationService->processApps(...$aliases);
        $rendered = $this->generationService->renderApps(...$aliases);
        $delimiter = PHP_EOL.'---'.PHP_EOL;
        $output = implode($delimiter, $rendered);

        $io->writeln($output.$delimiter);

        return self::SUCCESS;
    }
}
