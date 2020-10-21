<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Dealroadshow\K8S\Framework\Dumper\ProjectDumper;
use Dealroadshow\K8S\Framework\Project\ProjectInterface;
use Dealroadshow\K8S\Framework\Project\ProjectProcessor;
use Dealroadshow\K8S\Framework\Registry\ProjectRegistry;

class DumpProjectCommand extends Command
{
    private ProjectRegistry $registry;
    private ProjectProcessor $processor;
    private ProjectDumper $dumper;
    private string $manifestsDir;

    protected static $defaultName = 'dealroadshow_k8s:dump:project';

    public function __construct(ProjectRegistry $registry, ProjectProcessor $processor, ProjectDumper $dumper, string $manifestsDir)
    {
        $this->registry = $registry;
        $this->processor = $processor;
        $this->dumper = $dumper;
        $this->manifestsDir = $manifestsDir;

        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Processes project and dumps Yaml manifests to outpur dir')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Name of a project to dump'
            )
            ->addOption(
                'tag',
                't',
                InputOption::VALUE_REQUIRED,
                'Filter manifests by tag'
            )
            ->addArgument(
                'output_dir',
                InputArgument::REQUIRED,
                'Directory where to save generated Yaml manifests'
            )
            ->setAliases([
                'k8s:dump:project',
            ])
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $project = $this->getProject($input);
            $outputDir = $this->getValidOutputDir($input);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }
        $tag = $input->getOption('tag');
        $this->processor->process($project, $tag);
        $this->dumper->dump($project, $outputDir);

        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $outputDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function getProject(InputInterface $input): ProjectInterface
    {
        $projectName = $input->getArgument('project');
        if (!$this->registry->has($projectName)) {
            $validNames = array_map(
                fn (ProjectInterface $project) => $project->name(),
                $this->registry->all()
            );
            throw new InvalidArgumentException(
                sprintf(
                    'Project "%s" does not exist. Valid project names are: "%s"',
                    $projectName,
                    implode('", "', $validNames)
                )
            );
        }

        return $this->registry->get($projectName);
    }

    private function getValidOutputDir(InputInterface $input): string
    {
        $outputDir = $input->getArgument('output_dir');
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
