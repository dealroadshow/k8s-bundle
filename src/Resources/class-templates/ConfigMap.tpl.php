<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Data\Collection\StringMap;
use Dealroadshow\K8S\Framework\Core\ConfigMap\AbstractConfigMap;

class <?= $className; ?> extends AbstractConfigMap
{
    public function data(StringMap $data): void
    {
    }

    public static function shortName(): string
    {
        return '<?= $manifestName; ?>';
    }
}
