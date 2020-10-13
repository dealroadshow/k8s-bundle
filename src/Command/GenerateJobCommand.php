<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ClassGenerator\JobGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class GenerateJobCommand extends Command
{
    private const ARGUMENT_APP_NAME     = 'app-name';
    private const ARGUMENT_JOB_NAME     = 'job-name';

    protected static $defaultName = 'dealroadshow_k8s:generate:job';
    private JobGenerator $generator;

    public function __construct(JobGenerator $generator)
    {
        $this->generator = $generator;
        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Creates a new K8S Job skeleton')
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::REQUIRED,
                'App name without "app" suffix (e.g. <fg=yellow>cron-jobs</>)'
            )
            ->addArgument(
                self::ARGUMENT_JOB_NAME,
                InputArgument::REQUIRED,
                'Job name without "job" suffix (e.g. <fg=yellow>users-sync</>)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);
        $jobName = $input->getArgument(self::ARGUMENT_JOB_NAME);

        try {
            $fileName = $this->generator->generate($appName, $jobName);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return 1;
        }

        $io->success(
            sprintf('Job "%s" successfully generated, see file "%s"', $appName, $fileName)
        );
        $io->newLine();

        return 0;
    }
}
