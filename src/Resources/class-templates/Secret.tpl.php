<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Data\Collection\StringMap;
use Dealroadshow\K8S\Framework\Core\MetadataConfigurator;
use Dealroadshow\K8S\Framework\Core\Secret\AbstractSecret;

class <?= $className; ?> extends AbstractSecret
{
    public static function shortName(): string
    {
        return '<?= $manifestName; ?>';
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

    public function stringData(StringMap $binaryData): void
    {
    }
}
