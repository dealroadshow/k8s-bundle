<?php

declare(strict_types=1);

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\Checksum\Calculator\ChecksumCalculatorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ChecksumCalculatorPass implements CompilerPassInterface
{
    public const CHECKSUM_CALCULATOR_TAG = 'dealroadshow_k8s.checksum_calculator';

    public function process(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(ChecksumCalculatorInterface::class)
            ->addTag(self::CHECKSUM_CALCULATOR_TAG);
    }
}
