<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

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
    private AppRegistry $registry;
    private AppProcessor $appProcessor;
    private AppDumper $dumper;

    protected static $defaultName = 'dealroadshow_k8s:dump:app';

    public function __construct(AppRegistry $registry, AppProcessor $appProcessor, AppDumper $dumper)
    {
        $this->registry = $registry;
        $this->appProcessor = $appProcessor;
        $this->dumper = $dumper;

        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Processes app and dumps Yaml manifests to output dir.')
            ->addArgument(
                'app',
                InputArgument::REQUIRED,
                'Name of app to synthetize'
            )
            ->addArgument(
                'output_dir',
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
            $outputDir = $this->getValidOutputDir($input);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $app = $this->registry->get($appName);
        $this->appProcessor->process($app);
        $this->dumper->dump($app, $outputDir);

        $io->success(sprintf('Yaml manifests are saved to directory "%s"', $outputDir));
        $io->newLine();

        return self::SUCCESS;
    }

    private function getValidAppName(InputInterface $input): string
    {
        $appName = $input->getArgument('app');
        if (!$this->registry->has($appName)) {
            throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appName));
        }

        return $appName;
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
