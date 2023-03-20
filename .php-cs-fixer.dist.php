<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->exclude(['Resources/class-templates'])
    ->append([__FILE__]);

$config = new PhpCsFixer\Config();

return $config
    ->setRules(
        [
            '@PHP80Migration' => true,
            '@PHP80Migration:risky' => true,
            '@PHP81Migration' => true,
            '@PHP82Migration' => true,
            '@PSR12' => true,
            'protected_to_private' => false,
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
