<?php

namespace Dealroadshow\Bundle\K8SBundle\DependencyInjection\Compiler;

use Dealroadshow\Bundle\K8SBundle\Checksum\Calculator\ChecksumCalculatorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ChecksumCalculatorPass implements CompilerPassInterface
{
    const CHECKSUM_CALCULATOR_TAG = 'dealroadshow_k8s.checksum_calculator';

    public function process(ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(ChecksumCalculatorInterface::class)
            ->addTag(self::CHECKSUM_CALCULATOR_TAG);
    }
}
