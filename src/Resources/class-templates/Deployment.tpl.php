<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Container\AbstractContainer;
use Dealroadshow\K8S\Framework\Core\Container\Image\Image;
use Dealroadshow\K8S\Framework\Core\Deployment\AbstractDeployment;
use Dealroadshow\K8S\Framework\Core\LabelSelector\SelectorConfigurator;
use Dealroadshow\K8S\Framework\Core\Pod\Volume\VolumesConfigurator;

class <?php echo $className; ?> extends AbstractDeployment
{
    public function selector(SelectorConfigurator $selector): void
    {
    }

    public function containers(): iterable
    {
        yield new class extends AbstractContainer {
            public function name(): string
            {
                return '<?php echo $manifestName; ?>-container';
            }

            public function image(): Image
            {
                return Image::fromName('example/my-image-name');
            }
        };
    }

    public function volumes(VolumesConfigurator $volumes): void
    {
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
