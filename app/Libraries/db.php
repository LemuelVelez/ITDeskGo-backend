<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;

class db
{
    /**
     * Return a CodeIgniter database connection using MySQL_DATABASE_URL when available.
     */
    public static function connection(?string $databaseUrl = null): BaseConnection
    {
        return Database::connect(self::config($databaseUrl));
    }

    /**
     * Build a CodeIgniter-compatible database config array from MySQL_DATABASE_URL.
     *
     * Supported format:
     * mysql://username:password@hostname:3306/database?charset=utf8mb4&collation=utf8mb4_general_ci
     */
    public static function config(?string $databaseUrl = null): array
    {
        $databaseUrl = $databaseUrl ?: (string) env('MySQL_DATABASE_URL', '');

        if ($databaseUrl === '') {
            return (array) config('Database')->default;
        }

        $parts = parse_url($databaseUrl);

        if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
            throw new InvalidArgumentException('Invalid MySQL_DATABASE_URL value.');
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'mysql'));
        $driver = match ($scheme) {
            'mysql', 'mysqli' => 'MySQLi',
            default => throw new InvalidArgumentException('Only mysql:// and mysqli:// database URLs are supported.'),
        };

        $database = ltrim((string) $parts['path'], '/');

        if ($database === '') {
            throw new InvalidArgumentException('MySQL_DATABASE_URL must include a database name.');
        }

        return [
            'DSN'      => '',
            'hostname' => rawurldecode((string) $parts['host']),
            'username' => rawurldecode((string) ($parts['user'] ?? '')),
            'password' => rawurldecode((string) ($parts['pass'] ?? '')),
            'database' => rawurldecode($database),
            'DBDriver' => $driver,
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => ENVIRONMENT !== 'production',
            'charset'  => (string) ($query['charset'] ?? 'utf8mb4'),
            'DBCollat' => (string) ($query['collation'] ?? 'utf8mb4_general_ci'),
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => isset($parts['port']) ? (int) $parts['port'] : 3306,
        ];
    }

    public static function baseUrl(): string
    {
        return rtrim((string) env('app.baseURL', ''), '/');
    }
}
