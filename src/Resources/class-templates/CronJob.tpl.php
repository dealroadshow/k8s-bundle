<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Container\Image\Image;
use Dealroadshow\K8S\Framework\Core\CronJob\AbstractContainerCronJob;

class <?php echo $className; ?> extends AbstractContainerCronJob
{
    public function image(): Image
    {
        return Image::fromName('my-cool/image');
    }

    public function schedule(): string
    {
        return '* * * * *'; // min hour day month dayOfWeek
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
