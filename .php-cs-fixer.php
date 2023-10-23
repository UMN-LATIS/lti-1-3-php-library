<?php

require __DIR__.'/vendor/autoload.php';

return (new Jubeki\LaravelCodeStyle\Config())
    ->setFinder(
        \PhpCsFixer\Finder::create()
            ->ignoreVCS(true)
            ->ignoreVCSIgnored(true)
            ->in(__DIR__)
    )
    ->setRules([
        /* Packback-specific style preferences */
        'not_operator_with_successor_space' => false,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'only_if_meta',
                'property' => 'only_if_meta',
            ],
        ],
    ]);