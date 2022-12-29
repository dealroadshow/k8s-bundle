<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\AppGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dealroadshow_k8s:generate:app',
    description: 'Creates a new K8S app skeleton',
    aliases: ['k8s:generate:app', 'k8s:gen:app']
)]
class GenerateAppCommand extends Command
{
    private const ARGUMENT_APP_NAME = 'app-name';

    public function __construct(private AppGenerator $generator)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::REQUIRED,
                'App name without "app" suffix (e.g. "<fg=yellow>my</>" will result in MyApp class)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);

        try {
            $fileName = $this->generator->generate($appName);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $io->success(sprintf('App "%s" successfully generated, see file "%s"', $appName, $fileName));

        return self::SUCCESS;
    }
}
