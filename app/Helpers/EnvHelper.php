<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class EnvHelper
{
    public static function get(string $key, mixed $default = null, string $envPath = null): mixed
    {
        $envPath = $envPath ?? base_path('.env');

        if (!file_exists($envPath)) {
            return $default;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (Str::startsWith(trim($line), '#')) {
                continue;
            }

            [$name, $value] = array_map(function ($item) {
                return $item === null ? null : trim($item);
            }, explode('=', $line, 2) + [1 => null]);

            if ($name === $key) {
                return self::unescapeValue($value);
            }
        }

        return $default;
    }

    public static function set(string $key, mixed $value, string $envPath = null): bool
    {
        $envPath = $envPath ?? base_path('.env');
        $value = self::escapeValue($value);

        if (!file_exists($envPath)) {
            return false;
        }

        $contents = file_get_contents($envPath);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, "{$key}={$value}", $contents);
        } else {
            $contents = rtrim($contents, "\n") . "\n{$key}={$value}\n";
        }

        $written = file_put_contents($envPath, $contents) !== false;

        if ($written) {
            self::updateRuntimeEnv($key, $value);
        }

        return $written;
    }

    public static function delete(string $key, string $envPath = null): bool
    {
        $envPath = $envPath ?? base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES);
        $result = [];
        $deleted = false;

        foreach ($lines as $line) {
            if (Str::startsWith(trim($line), '#')) {
                $result[] = $line;
                continue;
            }

            [$name] = array_map(function ($item) {
                return $item === null ? null : trim($item);
            }, explode('=', $line, 2) + [1 => null]);

            if ($name === $key) {
                $deleted = true;
                continue;
            }

            $result[] = $line;
        }

        if (!$deleted) {
            return false;
        }

        $result = implode("\n", $result) . "\n";

        if (file_put_contents($envPath, $result) === false) {
            return false;
        }

        self::unsetRuntimeEnv($key);

        return true;
    }

    public static function exists(string $key, string $envPath = null): bool
    {
        $envPath = $envPath ?? base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (Str::startsWith(trim($line), '#')) {
                continue;
            }

            [$name] = array_map(function ($item) {
                return $item === null ? null : trim($item);
            }, explode('=', $line, 2) + [1 => null]);

            if ($name === $key) {
                return true;
            }
        }

        return false;
    }

    protected static function escapeValue(mixed $value): string
    {
        $value = (string) $value;

        if (preg_match('/\s|\#|\$/', $value)) {
            $value = '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    protected static function updateRuntimeEnv(string $key, mixed $value): void
    {
        $value = self::unescapeValue(self::escapeValue($value));

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    protected static function unsetRuntimeEnv(string $key): void
    {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }

    protected static function unescapeValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return stripslashes(substr($value, 1, -1));
        }

        return $value;
    }
}
