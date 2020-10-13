<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Job\AbstractJob;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Core\Pod\Containers\PodContainers;
use Dealroadshow\K8S\Framework\Core\Pod\Volume\VolumesConfigurator;

class <?= $className; ?> extends AbstractJob
{
    public static function name(): string
    {
        return '<?= $jobName; ?>';
    }

    public function fileNameWithoutExtension(): string
    {
        return '<?= $fileName; ?>';
    }

    public function configureMeta(MetadataConfigurator $meta): void
    {
    }

    public function containers(PodContainers $containers): void
    {
    }

    public function volumes(VolumesConfigurator $volumes): void
    {
    }
}
