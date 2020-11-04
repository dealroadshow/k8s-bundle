<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Deployment\AbstractDeployment;
use Dealroadshow\K8S\Framework\Core\LabelSelector\LabelSelectorConfigurator;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Core\Pod\Containers\PodContainers;
use Dealroadshow\K8S\Framework\Core\Pod\Volume\VolumesConfigurator;

class <?= $className; ?> extends AbstractDeployment
{
    public function labelSelector(LabelSelectorConfigurator $selector): void
    {
    }

    public function name(): string
    {
        return '<?= $manifestName; ?>';
    }

    public function fileNameWithoutExtension(): string
    {
        return '<?= $fileName; ?>';
    }

    public function containers(PodContainers $containers): void
    {
    }

    public function volumes(VolumesConfigurator $volumes): void
    {
    }
}
