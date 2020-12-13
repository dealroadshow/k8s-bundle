<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Service\AbstractService;
use Dealroadshow\K8S\Data\Collection\StringMap;
use Dealroadshow\K8S\Framework\Core\Service\Configurator\ServicePortsConfigurator;
use Dealroadshow\K8S\Framework\Core\Service\Configurator\ServiceTypeConfigurator;

class <?= $className; ?> extends AbstractService
{
    public function ports(ServicePortsConfigurator $ports): void
    {
    }

    public function selector(StringMap $selector): void
    {
    }

    public function type(ServiceTypeConfigurator $type): void
    {
    }

    public static function shortName(): string
    {
        return '<?= $manifestName; ?>';
    }
}
