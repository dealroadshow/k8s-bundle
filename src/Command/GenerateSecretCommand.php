<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator\SecretGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class GenerateSecretCommand extends Command
{
    private const ARGUMENT_APP_NAME     = 'app-name';
    private const ARGUMENT_SECRET_NAME     = 'secret-name';

    protected static $defaultName = 'dealroadshow_k8s:generate:secret';
    private SecretGenerator $generator;

    public function __construct(SecretGenerator $generator)
    {
        $this->generator = $generator;
        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Creates a new K8S Secret skeleton')
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::REQUIRED,
                'App name without "app" suffix (e.g. <fg=yellow>cron-jobs</>)'
            )
            ->addArgument(
                self::ARGUMENT_SECRET_NAME,
                InputArgument::REQUIRED,
                'Secret name without "secret" suffix (e.g. <fg=yellow>users-postgres</>)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);
        $depName = $input->getArgument(self::ARGUMENT_SECRET_NAME);

        try {
            $fileName = $this->generator->generate($appName, $depName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return 1;
        }

        $io->success(
            sprintf('Secret "%s" successfully generated, see file "%s"', $appName, $fileName)
        );
        $io->newLine();

        return 0;
    }
}
