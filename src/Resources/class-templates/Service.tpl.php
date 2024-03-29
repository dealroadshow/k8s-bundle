<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Service\AbstractService;
use Dealroadshow\K8S\Framework\Core\Service\Configurator\ServicePortsConfigurator;

class <?php echo $className; ?> extends AbstractService
{
    public function ports(ServicePortsConfigurator $ports): void
    {
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
