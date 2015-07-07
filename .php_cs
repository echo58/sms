<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->in(__DIR__)
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$fixers = [
    'blankline_after_open_tag',
    'concat_without_spaces',
    'operators_spaces',
    'remove_leading_slash_use',
    'short_array_syntax',
    'spaces_before_semicolon',
    'unused_use',
    'return',
    'duplicate_semicolon',
    'ordered_use',
];

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers($fixers)
    ->finder($finder)
    ->setUsingCache(true);
