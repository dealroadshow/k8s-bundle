<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use BadMethodCallException;
use Dealroadshow\K8S\Framework\Core\CronJob\AbstractCronJob;
use Dealroadshow\K8S\Framework\Core\Job\JobInterface;

class <?= $className; ?> extends AbstractCronJob
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
        return '<?= $manifestName; ?>';
    }
}
