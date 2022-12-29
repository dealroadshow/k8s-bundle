<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\AppGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateAppCommand extends Command
{
    use ClearCacheTrait;

    private const ARGUMENT_APP_NAME = 'app-name';

    protected static $defaultName = 'dealroadshow_k8s:generate:app';

    public function __construct(private AppGenerator $generator)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Creates a new K8S App skeleton')
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::REQUIRED,
                'App name without "app" suffix (e.g. <fg=yellow>cron-jobs</>)'
            )
            ->setAliases([
                'k8s:generate:app',
                'k8s:gen:app',
            ])
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);

        try {
            $fileName = $this->generator->generate($appName);
            $this->clearCache();
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $io->success(
            sprintf('App "%s" successfully generated, see file "%s"', $appName, $fileName)
        );
        $io->newLine();

        return self::SUCCESS;
    }
}
