<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Ingress\AbstractIngress;
use Dealroadshow\K8S\Framework\Core\Ingress\Configurator\IngressBackendFactory;
use Dealroadshow\K8S\Framework\Core\Ingress\Configurator\IngressRulesConfigurator;

class <?php echo $className; ?> extends AbstractIngress
{
    public function rules(IngressRulesConfigurator $rules, IngressBackendFactory $factory): void
    {
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
