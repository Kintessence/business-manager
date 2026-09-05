<?php
declare(strict_types=1);

namespace BusinessEngine;

final class Autoloader
{
    private const PREFIX = 'BusinessEngine\\';
    private const BASE_DIR = BE_PLUGIN_DIR;

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        if (!str_starts_with($class, self::PREFIX)) {
            return;
        }

        $relativeClass = substr($class, strlen(self::PREFIX));
        $parts = explode('\\', $relativeClass);
        $first = $parts[0] ?? '';

        if (in_array($first, ['Database', 'Enums', 'Support', 'Core', 'ModuleInterface'], true)) {
            $path = self::BASE_DIR . 'includes/' . str_replace('\\', '/', $relativeClass) . '.php';
        } else {
            $path = self::BASE_DIR . 'modules/' . str_replace('\\', '/', $relativeClass) . '.php';
        }

        if (file_exists($path)) {
            require_once $path;
        }
    }
}