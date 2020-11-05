<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Service\AbstractService;
use Dealroadshow\K8S\Data\Collection\StringMap;
use Dealroadshow\K8S\Framework\Core\Service\Configurator\ServicePortsConfigurator;
use Dealroadshow\K8S\Framework\Core\Service\Configurator\ServiceTypeConfigurator;

class <?= $className; ?> extends AbstractService
{
    public static function name(): string
    {
        return '<?= $manifestName; ?>';
    }

    public function fileNameWithoutExtension(): string
    {
        return '<?= $fileName; ?>';
    }

    public function ports(ServicePortsConfigurator $ports): void
    {
    }

    public function selector(StringMap $selector): void
    {
    }

    public function type(ServiceTypeConfigurator $type): void
    {
    }
}
