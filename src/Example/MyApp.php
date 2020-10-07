<?php

namespace Dealroadshow\Bundle\K8SBundle\Example;

use Dealroadshow\K8S\Framework\App\AbstractApp;

class MyApp extends AbstractApp
{
    public function name(): string
    {
        return 'myName';
    }
}