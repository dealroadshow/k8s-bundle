<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\App\AbstractApp;

class <?= $className; ?> extends AbstractApp
{
    public static function name(): string
    {
        return '<?= $appName; ?>';
    }

    public function manifestConfig(string $shortName): array
    {
        return [];
    }
}
