<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->append([__FILE__]);

$config = new PhpCsFixer\Config();

return $config
    ->setRules(
        [
            '@PHP80Migration' => true,
            '@PHP80Migration:risky' => true,
            '@Symfony' => true,
            'protected_to_private' => false,
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
