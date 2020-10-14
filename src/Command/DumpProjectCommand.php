<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return 1;
        }
        $this->processor->process($project);
        $projectDir = $this->manifestsDir.DIRECTORY_SEPARATOR.$project->name();
        $this->dumper->dump($project, $projectDir);

        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $projectDir));
        $io->newLine();

        return 0;
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
}
