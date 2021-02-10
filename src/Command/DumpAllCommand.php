<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\App\AppProcessor;
use Dealroadshow\K8S\Framework\Dumper\AppDumper;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DumpAllCommand extends Command
{
    protected static $defaultName = 'dealroadshow_k8s:dump:all';

    public function __construct(
        private AppRegistry $registry,
        private AppProcessor $processor,
        private AppDumper $dumper,
        private string $manifestsDir
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription('Dumps all apps and their manifests to Yaml files')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->registry->aliases() as $alias) {
            $this->processor->process($alias);
            $dir = $this->manifestsDir.DIRECTORY_SEPARATOR.$alias;
            $this->dumper->dump($alias, $dir);
        }

        $io->success(sprintf('Manifests saved to directory "%s"', $this->manifestsDir));
        $io->newLine();

        return self::SUCCESS;
    }
}
