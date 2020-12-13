<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Job\AbstractJob;
use Dealroadshow\K8S\Framework\Core\Pod\Containers\PodContainers;
use Dealroadshow\K8S\Framework\Core\Pod\Volume\VolumesConfigurator;

class <?= $className; ?> extends AbstractJob
{
    public function containers(PodContainers $containers): void
    {
    }

    public function volumes(VolumesConfigurator $volumes): void
    {
    }

    public static function shortName(): string
    {
        return '<?= $manifestName; ?>';
    }
}
