<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class BeforeMethod extends AbstractMethodDecoratorAttribute
{
}
