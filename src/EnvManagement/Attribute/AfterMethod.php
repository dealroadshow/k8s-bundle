<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AfterMethod extends AbstractMethodDecoratorAttribute
{
}
