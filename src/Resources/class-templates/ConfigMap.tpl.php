<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Data\Collection\StringMap;
use Dealroadshow\K8S\Framework\Core\ConfigMap\AbstractConfigMap;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;

class <?= $className; ?> extends AbstractConfigMap
{
    public static function name(): string
    {
        return '<?= $configMapName; ?>';
    }

    public function fileNameWithoutExtension(): string
    {
        return '<?= $fileName; ?>';
    }

    public function configureMeta(MetadataConfigurator $meta): void
    {
    }

    public function data(StringMap $data): void
    {
    }

    public function binaryData(StringMap $binaryData): void
    {
    }
}
