<?php

use App\Helpers\EnvHelper;

if (!function_exists('env_get')) {
    function env_get(string $key, mixed $default = null, ?string $path = null): mixed
    {
        return EnvHelper::get($key, $default, $path);
    }
}

if (!function_exists('env_set')) {
    function env_set(string $key, mixed $value, ?string $path = null): bool
    {
        return EnvHelper::set($key, $value, $path);
    }
}

if (!function_exists('env_delete')) {
    function env_delete(string $key, ?string $path = null): bool
    {
        return EnvHelper::delete($key, $path);
    }
}

if (!function_exists('env_exists')) {
    function env_exists(string $key, ?string $path = null): bool
    {
        return EnvHelper::exists($key, $path);
    }
}
