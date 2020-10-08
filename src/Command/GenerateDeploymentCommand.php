<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator\DeploymentGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class GenerateDeploymentCommand extends Command
{
    private const ARGUMENT_APP_NAME     = 'app-name';
    private const ARGUMENT_DEPLOYMENT_NAME     = 'deployment-name';

    protected static $defaultName = 'dealroadshow_k8s:generate:deployment';
    private DeploymentGenerator $generator;

    public function __construct(DeploymentGenerator $generator)
    {
        $this->generator = $generator;
        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Creates a new K8S Deployment skeleton')
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::REQUIRED,
                'App name without "app" suffix (e.g. <fg=yellow>cron-jobs</>)'
            )
            ->addArgument(
                self::ARGUMENT_DEPLOYMENT_NAME,
                InputArgument::REQUIRED,
                'Deployment name without "app" suffix (e.g. <fg=yellow>cron-jobs-deployment</>)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);
        $depName = $input->getArgument(self::ARGUMENT_DEPLOYMENT_NAME);

        try {
            $fileName = $this->generator->generate($appName, $depName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return 1;
        }

        $io->success(
            sprintf('App "%s" successfully generated, see file "%s"', $appName, $fileName)
        );
        $io->newLine();

        return 0;
    }
}
