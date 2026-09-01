<?php
declare(strict_types=1);

namespace BusinessEngine\Support;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            if (!str_starts_with($class, 'BusinessEngine\\')) {
                return;
            }

            $relative = substr($class, strlen('BusinessEngine\\'));
            $parts = explode('\\', $relative);

            $path = (in_array($parts[0], ['Database', 'Contracts', 'Support', 'Core'], true))
                ? BE_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relative) . '.php'
                : BE_PLUGIN_DIR . 'modules/' . str_replace('\\', '/', $relative) . '.php';

            if (file_exists($path)) {
                require_once $path;
            }
        });
    }
}
