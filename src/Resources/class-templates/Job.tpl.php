<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Container\Image\Image;
use Dealroadshow\K8S\Framework\Core\Job\AbstractContainerJob;

class <?php echo $className; ?> extends AbstractContainerJob
{
    public function image(): Image
    {
        return Image::fromName('my-cool/image');
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
