<?php

if (true === (require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php') || empty($_SERVER['SCRIPT_FILENAME'])) {
    return;
}

$app = require $_SERVER['SCRIPT_FILENAME'];

if (!is_object($app)) {
    throw new TypeError(sprintf('Invalid return value: callable object expected, "%s" returned from "%s".', get_debug_type($app), $_SERVER['SCRIPT_FILENAME']));
}

if (!is_dir(__DIR__ . DIRECTORY_SEPARATOR . 'vendor') || !is_file(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    throw new LogicException('Dependencies are missing. Try running "composer install".');
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$argv ??= $_SERVER['argv'] ?? [];
foreach ($argv as $arg) {
    if (!\is_scalar($arg) && !$arg instanceof Stringable) {
        throw new RuntimeException(\sprintf('Argument values expected to be all scalars, got "%s".', get_debug_type($arg)));
    }
}
// strip the application name
array_shift($argv);

exit($app($argv, __DIR__));
