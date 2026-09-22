<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = new Finder()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('tests/Support/_generated')
    ->exclude('tests/_output')
;

/**
 * @see https://cs.symfony.com/doc/rules/index.html
 */
return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        /**@see https://cs.symfony.com/doc/ruleSets/Symfony.html */
        '@Symfony' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => false, 'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters']],
        'phpdoc_to_comment' => false,
        'phpdoc_annotation_without_dot' => true,
        'global_namespace_import' => ['import_classes' => true],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'concat_space' => ['spacing' => 'one'],
        'echo_tag_syntax' => ['format' => 'short'],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_abstract',
                'method_protected_abstract',
                'method_private_abstract',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
    ])
    ->setCacheFile(__DIR__ . 'var/cache/php-cs-fixer.cache')
    ->setFinder($finder);
