<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Collection\StringMap;
use Dealroadshow\K8S\Framework\Core\Secret\AbstractSecret;

class <?php echo $className; ?> extends AbstractSecret
{
    public function data(StringMap $data): void
    {
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
