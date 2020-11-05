<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\CronJob\AbstractCronJob;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Core\Job\JobInterface;

class <?= $className; ?> extends AbstractCronJob
{
    public static function name(): string
    {
        return '<?= $manifestName; ?>';
    }

    public function fileNameWithoutExtension(): string
    {
        return '<?= $fileName; ?>';
    }

    public function job(): JobInterface
    {
    }

    public function schedule(): string
    {
        return '* * * * *'; // min hour day month dayOfWeek
    }

    public function configureMeta(MetadataConfigurator $meta): void
    {
    }
}
