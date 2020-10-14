<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context\ContextInterface;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\ContextRegistry;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\ManifestGenerator;
use Dealroadshow\Bundle\K8SBundle\Util\Str;
use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class GenerateManifestCommand extends Command
{
    private const ARGUMENT_APP_NAME      = 'app-name';
    private const ARGUMENT_MANIFEST_TYPE = 'manifest-type';
    private const ARGUMENT_MANIFEST_NAME = 'manifest-name';

    private ManifestGenerator $generator;
    private AppRegistry $appRegistry;
    private ContextRegistry $contextRegistry;

    protected static $defaultName = 'dealroadshow_k8s:generate:manifest';
    /**
     * @var ManifestRegistry
     */
    private ManifestRegistry $manifestRegistry;

    public function __construct(AppRegistry $appRegistry, ManifestRegistry $manifestRegistry, ContextRegistry $contextRegistry, ManifestGenerator $generator)
    {
        $this->appRegistry = $appRegistry;
        $this->manifestRegistry = $manifestRegistry;
        $this->contextRegistry = $contextRegistry;
        $this->generator = $generator;

        parent::__construct(null);
    }

    public function configure()
    {
        $this
            ->setDescription('Creates a new Manifest skeleton')
            ->addArgument(
                self::ARGUMENT_MANIFEST_NAME,
                InputArgument::REQUIRED,
                'Manifest name without type suffix (e.g. <fg=yellow>users-sync</>, but not <fg=red>users-sync</>)'
            )
            ->addArgument(
                self::ARGUMENT_MANIFEST_TYPE,
                InputArgument::OPTIONAL,
                'Manifest type (e.g. <fg=yellow>deployment</> or <fg=yellow>config-map</>)'
            )
            ->addArgument(
                self::ARGUMENT_APP_NAME,
                InputArgument::OPTIONAL,
                'App name without "app" suffix (e.g. <fg=yellow>drs-cron</>)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $context = $this->getContext($input, $io);
            $app = $this->getApp($input, $io);
            $name = $this->getManifestName($input, $context, $app);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        try {
            $fileName = $this->generator->generate($name, $context, $app);
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        $io->success(
            sprintf(
                '%s "%s" successfully generated, see file "%s"',
                $context->kind(),
                $name,
                $fileName
            )
        );
        $io->newLine();

        return self::SUCCESS;
    }

    private function getApp(InputInterface $input, SymfonyStyle $io): AppInterface
    {
        $appName = $input->getArgument(self::ARGUMENT_APP_NAME);

        if (null === $appName) {
            $question = new ChoiceQuestion(
                'Please choose a name of app where you want to generate new manifest class',
                $this->appRegistry->names()
            );
            $appName = $io->askQuestion($question);
        }

        if (!$this->appRegistry->has($appName)) {
            throw new InvalidArgumentException(sprintf('App "%s" does not exist', $appName));
        }

        return $this->appRegistry->get($appName);
    }

    private function getContext(InputInterface $input, SymfonyStyle $io): ContextInterface
    {
        $typeName = $input->getArgument(self::ARGUMENT_MANIFEST_TYPE);

        $supportedTypes = array_map(
            fn (string $kind) => Str::asDNSSubdomain($kind),
            $this->contextRegistry->kinds()
        );

        if (null === $typeName) {
            $question = new ChoiceQuestion(
                'Please choose a manifest type',
                $supportedTypes
            );
            $typeName = $io->askQuestion($question);
        }

        $kind = Str::asClassName($typeName);
        if (!$this->contextRegistry->has($kind)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Manifest type "%s" is not supported. Supported types: "%s"',
                    $typeName,
                    implode('", "', $supportedTypes)
                )
            );
        }

        return $this->contextRegistry->get($kind);
    }

    private function getManifestName(InputInterface $input, ContextInterface $context, AppInterface $app): string
    {
        $name = $input->getArgument(self::ARGUMENT_MANIFEST_NAME);
        if (!Str::isValidDNSSubdomain($name)) {
            $errTemplate = <<<'ERR'
            Name "%s" is not valid for manifest name. It must be valid DNS subdomain name, i.e. it must:
            - contain no more than 253 characters
            - contain only lowercase alphanumeric characters, '-' or '.'
            - start with an alphanumeric character
            - end with an alphanumeric character
            ERR;

            throw new InvalidArgumentException(
                sprintf($errTemplate, $name)
            );
        }

        $manifest = $this->manifestRegistry->query()
            ->app($app)
            ->instancesOf($context->parentInterface())
            ->name($name)
            ->getFirstResult();

        if (null !== $manifest) {
            throw new InvalidArgumentException(
                sprintf('Name "%s" is already taken by "%s"', $name, get_class($manifest))
            );
        }

        return $name;
    }
}
