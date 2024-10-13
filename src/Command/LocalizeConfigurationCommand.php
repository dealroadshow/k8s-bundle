<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\K8S\Framework\App\Integration\EnvSourcesRegistry;
use Dealroadshow\K8S\Framework\App\Integration\Localization\ExternalConfigurationLocalizer;
use Dealroadshow\K8S\Framework\ManifestGenerator\ManifestsGenerationService;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'dealroadshow_k8s:localize:configuration',
    description: 'Localizes all external configurations (ConfigMaps, Secrets) for the specified apps',
    aliases: ['k8s:localize:configuration', 'k8s:localize:config']
)]
class LocalizeConfigurationCommand extends Command
{
    public function __construct(
        private readonly ExternalConfigurationLocalizer $localizer,
        private readonly AppRegistry $appRegistry,
        private readonly EnvSourcesRegistry $envSourcesRegistry,
        private readonly ManifestsGenerationService $mg,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'apps',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Apps (aliases) to localize configurations for'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mg->processAll();

        $aliases = $input->getArgument('apps');
        $apps = in_array('all', $aliases)
            ? $this->appRegistry->all()
            : array_map(fn (string $appAlias) => $this->appRegistry->get($appAlias), $aliases);

        foreach ($apps as $app) {
            $this->localizer->localizeDependencies(
                $app->alias(),
                $this->envSourcesRegistry->getForApp($app->alias())
            );
        }

        return self::SUCCESS;
    }
}
