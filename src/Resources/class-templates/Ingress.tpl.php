<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Ingress\AbstractIngress;
use Dealroadshow\K8S\Framework\Core\Ingress\Configurator\IngressBackendFactory;
use Dealroadshow\K8S\Framework\Core\Ingress\Configurator\IngressRulesConfigurator;

class <?= $className; ?> extends AbstractIngress
{
    public static function name(): string
    {
        return '<?= $manifestName; ?>';
    }

    public function fileNameWithoutExtension(): string
    {
        return '<?= $fileName; ?>';
    }

    public function rules(IngressRulesConfigurator $rules, IngressBackendFactory $factory): void
    {
    }
}
