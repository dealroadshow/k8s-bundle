<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class AfterMethod extends AbstractMethodDecoratorAttribute
{
}
