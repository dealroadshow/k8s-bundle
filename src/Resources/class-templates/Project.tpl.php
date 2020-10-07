<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Project\AbstractProject;

class <?= $className; ?> extends AbstractProject
{
    /**
    * @return AppInterface[]|iterable
    */
    public function apps(): iterable
    {
        return [];
    }

    public function name(): string
    {
        return '<?= $projectName; ?>';
    }
}
