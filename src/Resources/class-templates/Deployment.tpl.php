<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Dealroadshow\K8S\Framework\Core\Container\Image\Image;
use Dealroadshow\K8S\Framework\Core\Deployment\AbstractContainerDeployment;
use Dealroadshow\K8S\Framework\Core\LabelSelector\SelectorConfigurator;

class <?php echo $className; ?> extends AbstractContainerDeployment
{
    public function image(): Image
    {
        return Image::fromName('my-cool/image');
    }

    public function selector(SelectorConfigurator $selector): void
    {
        $selector
            ->addLabel('app', $this->app->alias())
            ->addLabel('name', static::shortName())
        ;
    }

    public static function shortName(): string
    {
        return '<?php echo $manifestName; ?>';
    }
}
