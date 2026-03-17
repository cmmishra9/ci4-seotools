<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->notPath('bootstrap.php');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                        => true,
        '@PHP81Migration'               => true,

        // Strict types
        'declare_strict_types'          => true,
        'strict_param'                  => true,
        'strict_comparison'             => true,

        // Imports
        'ordered_imports'               => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'             => true,
        'global_namespace_import'       => ['import_classes' => false, 'import_functions' => false],

        // Arrays
        'array_syntax'                  => ['syntax' => 'short'],
        'trailing_comma_in_multiline'   => ['elements' => ['arrays', 'arguments', 'parameters']],
        'no_multiline_whitespace_around_double_arrow' => true,

        // Strings
        'single_quote'                  => true,
        'explicit_string_variable'      => true,

        // Blank lines & spacing
        'blank_line_before_statement'   => ['statements' => ['return', 'throw', 'if', 'foreach', 'for']],
        'method_chaining_indentation'   => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],

        // PHPDoc
        'phpdoc_align'                  => ['align' => 'left'],
        'phpdoc_no_empty_return'        => true,
        'phpdoc_scalar'                 => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_trim'                   => true,
        'phpdoc_types'                  => true,
        'no_superfluous_phpdoc_tags'    => ['allow_mixed' => true],

        // Misc
        'native_function_invocation'    => ['include' => ['@internal']],
        'no_useless_else'               => true,
        'no_useless_return'             => true,
        'return_assignment'             => true,
        'yoda_style'                    => false,
    ])
    ->setFinder($finder);
