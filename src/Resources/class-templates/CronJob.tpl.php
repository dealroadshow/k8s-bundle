<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use BadMethodCallException;
use Dealroadshow\K8S\Framework\Core\CronJob\AbstractCronJob;
use Dealroadshow\K8S\Framework\Core\Job\JobInterface;

class <?php echo $className; ?> extends AbstractCronJob
{
    public function job(): JobInterface
    {
        throw new BadMethodCallException('Method job() must be implemented by user.');
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
