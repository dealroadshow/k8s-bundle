<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Container\Image\Image;
use Dealroadshow\K8S\Framework\Core\Deployment\AbstractContainerDeployment;

class <?php echo $className; ?> extends AbstractContainerDeployment
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
