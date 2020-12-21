<?php

namespace Dealroadshow\Bundle\K8SBundle\Command;

use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\Context\ContextInterface;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\ContextRegistry;
use Dealroadshow\Bundle\K8SBundle\CodeGeneration\ManifestGenerator\ManifestGenerator;
use Dealroadshow\K8S\Framework\Registry\AppRegistry;
use Dealroadshow\K8S\Framework\Registry\ManifestRegistry;
use Dealroadshow\K8S\Framework\Util\Str;
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
    use ClearCacheTrait;

    private const ARGUMENT_APP_NAME      = 'app-name';
    private const ARGUMENT_MANIFEST_TYPE = 'manifest-type';
    private const ARGUMENT_MANIFEST_NAME = 'manifest-name';

    protected static $defaultName = 'dealroadshow_k8s:generate:manifest';

    public function __construct(
        private AppRegistry $appRegistry,
        private ManifestRegistry $manifestRegistry,
        private ContextRegistry $contextRegistry,
        private ManifestGenerator $generator
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription('Creates a new Manifest skeleton')
            ->addArgument(
                self::ARGUMENT_MANIFEST_NAME,
                InputArgument::REQUIRED,
                'Manifest short name without type suffix (e.g. <fg=yellow>users-sync</>)'
            )
            ->addArgument(
                self::ARGUMENT_MANIFEST_TYPE,
                InputArgument::OPTIONAL,
                'Manifest type (e.g. <fg=yellow>deployment</> or <fg=yellow>config-map</>)'
            )
            ->setAliases([
                'k8s:generate:manifest',
                'k8s:gen:manifest',
                'k8s:gen:man',
            ])
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $context = $this->getContext($input, $io);
            $appAlias = $this->getAppAlias($io);
            $app = $this->appRegistry->get($appAlias);
            $name = $this->getManifestName($input, $context, $app);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $io->newLine();

            return self::FAILURE;
        }

        try {
            $fileName = $this->generator->generate($name, $context, $app);
            $this->clearCache();
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

    private function getAppAlias(SymfonyStyle $io): string
    {
        $question = new ChoiceQuestion(
            'Please choose an app, for which you want to generate new manifest class',
            array_values($this->appRegistry->classes())
        );
        $appClass = $io->askQuestion($question);

        foreach ($this->appRegistry->allAppsByClass($appClass) as $alias => $app) {
            return $alias;
        }

        throw new InvalidArgumentException(sprintf('App for class name "%s" does not exist', $appClass));
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

    private function getManifestName(InputInterface $input, ContextInterface $context, string $appAlias): string
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

        $manifest = $this->manifestRegistry->query($appAlias)
            ->instancesOf($context->parentInterface())
            ->shortName($name)
            ->getFirstResult();

        if (null !== $manifest) {
            throw new InvalidArgumentException(
                sprintf('Name "%s" is already taken by "%s"', $name, get_class($manifest))
            );
        }

        return $name;
    }
}
