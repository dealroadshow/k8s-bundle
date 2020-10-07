<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\App\AbstractApp;

class <?= $className; ?> extends AbstractApp
{
    public function name(): string
    {
        return '<?= $appName; ?>';
    }
}
