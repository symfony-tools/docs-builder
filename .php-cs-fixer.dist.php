<?php

$fileHeaderComment = <<<EOT
This file is part of the Guides SymfonyExtension package.

(c) Wouter de Jong

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOT;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP71Migration' => true,
        '@PHPUnit75Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'native_constant_invocation' => ['strict' => false],
        'no_superfluous_phpdoc_tags' => [
            'remove_inheritdoc' => true,
            'allow_unused_params' => true, // for future-ready params, to be replaced with https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/7377
        ],
        'header_comment' => ['header' => $fileHeaderComment],
        'modernize_strpos' => true,
        'get_class_to_class_keyword' => true,
        'nullable_type_declaration' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__.'/src')
    )
;
