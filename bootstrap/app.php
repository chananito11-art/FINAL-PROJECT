<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$databaseUrl = getenv('DATABASE_URL') ?: getenv('DB_URL');

if ($databaseUrl) {
    $parsed = parse_url($databaseUrl);

    if ($parsed !== false) {
        if (isset($parsed['scheme'])) {
            $scheme = $parsed['scheme'];
            if (!getenv('DB_CONNECTION')) {
                putenv("DB_CONNECTION={$scheme}");
                $_ENV['DB_CONNECTION'] = $scheme;
                $_SERVER['DB_CONNECTION'] = $scheme;
            }
        }

        if (isset($parsed['host'])) {
            putenv("DB_HOST={$parsed['host']}");
            $_ENV['DB_HOST'] = $parsed['host'];
            $_SERVER['DB_HOST'] = $parsed['host'];
        }

        if (isset($parsed['port'])) {
            putenv("DB_PORT={$parsed['port']}");
            $_ENV['DB_PORT'] = $parsed['port'];
            $_SERVER['DB_PORT'] = $parsed['port'];
        }

        if (isset($parsed['user'])) {
            putenv("DB_USERNAME={$parsed['user']}");
            $_ENV['DB_USERNAME'] = $parsed['user'];
            $_SERVER['DB_USERNAME'] = $parsed['user'];
        }

        if (isset($parsed['pass'])) {
            putenv("DB_PASSWORD={$parsed['pass']}");
            $_ENV['DB_PASSWORD'] = $parsed['pass'];
            $_SERVER['DB_PASSWORD'] = $parsed['pass'];
        }

        if (isset($parsed['path'])) {
            $database = ltrim($parsed['path'], '/');
            if ($database !== '') {
                putenv("DB_DATABASE={$database}");
                $_ENV['DB_DATABASE'] = $database;
                $_SERVER['DB_DATABASE'] = $database;
            }
        }
    }
}

if (empty(getenv('APP_KEY'))) {
    $key = 'base64:' . base64_encode(random_bytes(32));

    if (file_exists($envPath) && is_writable($envPath)) {
        $contents = file_get_contents($envPath);
        if (preg_match('/^APP_KEY=.*$/m', $contents)) {
            $contents = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $contents);
        } else {
            $contents = rtrim($contents, "\n") . "\nAPP_KEY={$key}\n";
        }
        file_put_contents($envPath, $contents);
    }

    putenv("APP_KEY={$key}");
    $_ENV['APP_KEY'] = $key;
    $_SERVER['APP_KEY'] = $key;
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
