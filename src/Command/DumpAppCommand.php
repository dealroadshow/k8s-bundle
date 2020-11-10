<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use Dealroadshow\K8S\Framework\Registry\ProjectRegistry;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Dealroadshow\K8S\Framework\App\AppProcessor;
use Dealroadshow\K8S\Framework\Dumper\AppDumper;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;

class DumpAppCommand extends Command
{
    private const ARGUMENT_PROJECT_NAME = 'project_name';
    private const ARGUMENT_APP_NAME = 'app_name';
    private const ARGUMENT_OUTPUT_DIR = 'output_dir';

    private ProjectRegistry $projectRegistry;
    private AppRegistry $appRegistry;
    private AppProcessor $appProcessor;
    private AppDumper $dumper;

    protected static $defaultName = 'dealroadshow_k8s:dump:app';

    public function __construct(ProjectRegistry $projectRegistry, AppRegistry $appRegistry, AppProcessor $appProcessor, AppDumper $dumper)
    {
        $this->projectRegistry = $projectRegistry;
        $this->appRegistry = $appRegistry;
        $this->appProcessor = $appProcessor;
        $this->dumper = $dumper;

        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Processes app and dumps Yaml manifests to output dir.')
            ->addArgument(
                self::ARGUMENT_PROJECT_NAME,
                InputArgument::REQUIRED,
                'Project name without "project" suffix (e.g. <fg=yellow>k8s-is-awesome</>)'
            )
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::REQUIRED,
                'Name of app to synthetize'
            )
            ->addArgument(
                self::ARGUMENT_OUTPUT_DIR,
                InputArgument::REQUIRED,
                'Directory where to save generated Yaml manifests'
            )
            ->setAliases([
                'k8s:dump:app',
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $appName = $this->getValidAppName($input);
            $projectName = $this->getValidProjectName($input);
            $outputDir = $this->getValidOutputDir($input);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $project = $this->projectRegistry->get($projectName);
        if(null === $project) {
            throw new InvalidArgumentException(sprintf('Project "%s" does not exist', $projectName));
        }
        $app = $this->getApp($project, $appName);
        $this->appProcessor->process($app, $project);
        $this->dumper->dump($app, $outputDir);

        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $outputDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function getApp(ProjectInterface $project, string $appName): AppInterface
    {
        foreach ($project->apps() as $app) {
            if($app->name() == $appName) {
                return $app;
            }
        }
        throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appName));
    }

    private function getValidAppName(InputInterface $input): string
    {
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);
        if (!$this->appRegistry->has($appName)) {
            throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appName));
        }

        return $appName;
    }

    private function getValidProjectName(InputInterface $input): string
    {
        $appName = $input->getArgument(self::ARGUMENT_PROJECT_NAME);
        if (!$this->appRegistry->has($appName)) {
            throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appName));
        }

        return $appName;
    }

    private function getValidOutputDir(InputInterface $input): string
    {
        $outputDir = $input->getArgument(self::ARGUMENT_OUTPUT_DIR);
        $outputDir = realpath($outputDir);
        if (!file_exists($outputDir)) {
            throw new InvalidArgumentException(sprintf('Output dir "%s" does not exist', $outputDir));
        }
        if (!is_dir($outputDir)) {
            throw new InvalidArgumentException(sprintf('Output path "%s" is not a directory', $outputDir));
        }

        return $outputDir;
    }
}
