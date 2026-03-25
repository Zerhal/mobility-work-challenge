<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

/**
 * PHP CS Fixer configuration.
 *
 * Rules follow Symfony + PSR-12 conventions with PHP 8.1+ additions.
 * Run manually:   ./vendor/bin/php-cs-fixer fix
 * Check only:     ./vendor/bin/php-cs-fixer fix --dry-run --diff
 */

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                                  => true,
        '@Symfony:risky'                            => true,
        '@PHP81Migration'                           => true,
        '@PHP80Migration:risky'                     => true,

        // Declarations
        'declare_strict_types'                      => true,
        'strict_param'                              => true,
        'strict_comparison'                         => true,

        // Imports
        'ordered_imports'                           => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                         => true,
        'global_namespace_import'                   => [
            'import_classes' => true,
            'import_functions' => false,
            'import_constants' => false,
        ],

        // Arrays
        'array_syntax'                              => ['syntax' => 'short'],
        'trailing_comma_in_multiline'               => ['elements' => ['arrays', 'arguments', 'parameters']],

        // Comments & docs
        'phpdoc_align'                              => ['align' => 'left'],
        'phpdoc_order'                              => true,
        'phpdoc_separation'                         => true,
        'no_superfluous_phpdoc_tags'                => ['allow_mixed' => true],

        // Operators
        'concat_space'                              => ['spacing' => 'one'],
        'not_operator_with_successor_space'         => true,

        // Misc
        'single_line_throw'                         => false,
        'yoda_style'                                => false,
        'increment_style'                           => ['style' => 'post'],
        'native_function_invocation'                => ['include' => ['@compiler_optimized']],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
