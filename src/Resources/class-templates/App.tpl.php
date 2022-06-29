<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\App\AbstractApp;

class <?php echo $className; ?> extends AbstractApp
{
    public static function name(): string
    {
        return '<?php echo $appName; ?>';
    }

    public function manifestConfig(string $shortName): array;
    {
        return [];
    }
}
