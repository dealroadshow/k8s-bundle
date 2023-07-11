<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\ManifestGenerator\ManifestsGenerationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dealroadshow_k8s:print:all',
    description: 'Prints all manifests to stdout',
    aliases: ['k8s:print:all']
)]
class PrintAllCommand extends Command
{
    public function __construct(private readonly ManifestsGenerationService $generationService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->generationService->processAll();
        $rendered = $this->generationService->renderAll();
        $delimiter = PHP_EOL.'---'.PHP_EOL;
        $output = implode($delimiter, $rendered);

        $io->writeln($output.$delimiter);

        return self::SUCCESS;
    }
}
