<?php

namespace Dealroadshow\Bundle\K8SBundle\Example;

use Dealroadshow\K8S\Framework\App\AppInterface;
use Dealroadshow\K8S\Framework\Project\AbstractProject;

class MyProject extends AbstractProject
{

    /**
     * @return iterable
     */
    public function apps(): iterable
    {
        return [];
    }

    public function name(): string
    {
        return '';
    }
}