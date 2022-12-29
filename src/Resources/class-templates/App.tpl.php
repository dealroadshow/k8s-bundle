<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\App\AbstractApp;

class <?php echo $className; ?> extends AbstractApp
{
    public static function name(): string
    {
        return '<?php echo $appName; ?>';
    }
}
