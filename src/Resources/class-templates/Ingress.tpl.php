<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Ingress\AbstractIngress;
use Dealroadshow\K8S\Framework\Core\Ingress\Configurator\IngressBackendFactory;
use Dealroadshow\K8S\Framework\Core\Ingress\Configurator\IngressRulesConfigurator;

class <?= $className; ?> extends AbstractIngress
{
    public function rules(IngressRulesConfigurator $rules, IngressBackendFactory $factory): void
    {
    }

    public static function shortName(): string
    {
        return '<?= $manifestName; ?>';
    }
}
