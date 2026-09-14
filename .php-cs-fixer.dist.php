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
    ])
    ->setCacheFile(__DIR__ . 'var/cache/php-cs-fixer.cache')
    ->setFinder($finder);
