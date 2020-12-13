<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Deployment\AbstractDeployment;
use Dealroadshow\K8S\Framework\Core\LabelSelector\SelectorConfigurator;
use Dealroadshow\K8S\Framework\Core\Pod\Containers\PodContainers;
use Dealroadshow\K8S\Framework\Core\Pod\Volume\VolumesConfigurator;

class <?= $className; ?> extends AbstractDeployment
{
    public function selector(LabelSelectorConfigurator $selector): void
    {
    }

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
