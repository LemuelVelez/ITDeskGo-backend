<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    //    /**
    //     * Sample database connection for SQLite3.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for Postgre.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for SQLSRV.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for OCI8.
    //     *
    //     * You may need the following environment variables:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/FREEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->applyRuntimeDatabaseConfig();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }

    private function applyRuntimeDatabaseConfig(): void
    {
        $this->applyDatabaseUrlConfig();

        $this->setDefaultValue('DBDriver', 'DB_DRIVER', 'DATABASE_DEFAULT_DBDRIVER', 'database.default.DBDriver');
        $this->setDefaultValue('hostname', 'DB_HOST', 'DATABASE_DEFAULT_HOSTNAME', 'database.default.hostname');
        $this->setDefaultValue('database', 'DB_NAME', 'DATABASE_DEFAULT_DATABASE', 'database.default.database');
        $this->setDefaultValue('username', 'DB_USER', 'DATABASE_DEFAULT_USERNAME', 'database.default.username');
        $this->setDefaultValue('password', 'DB_PASS', 'DATABASE_DEFAULT_PASSWORD', 'database.default.password');

        $port = $this->readEnvValue('DB_PORT', 'DATABASE_DEFAULT_PORT', 'database.default.port');

        if ($port !== null) {
            $this->default['port'] = (int) $port;
        }

        $debug = $this->readEnvValue('DB_DEBUG', 'DATABASE_DEFAULT_DBDEBUG', 'database.default.DBDebug');

        if ($debug !== null) {
            $this->default['DBDebug'] = filter_var($debug, FILTER_VALIDATE_BOOLEAN);
        } elseif (ENVIRONMENT === 'production') {
            $this->default['DBDebug'] = false;
        }
    }

    private function applyDatabaseUrlConfig(): void
    {
        $databaseUrl = $this->readEnvValue('MySQL_DATABASE_URL', 'MYSQL_DATABASE_URL', 'DATABASE_URL');

        if ($databaseUrl === null) {
            return;
        }

        $databaseUrl = trim($databaseUrl, " \t\n\r\0\x0B\"'");

        if ($databaseUrl === '') {
            return;
        }

        $parts = parse_url($databaseUrl);

        if ($parts === false) {
            return;
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';

        if (in_array($scheme, ['mysql', 'mysqli', 'mariadb'], true)) {
            $this->default['DBDriver'] = 'MySQLi';
        }

        if (isset($parts['host']) && $parts['host'] !== '') {
            $this->default['hostname'] = $parts['host'];
        }

        if (isset($parts['user'])) {
            $this->default['username'] = rawurldecode($parts['user']);
        }

        if (isset($parts['pass'])) {
            $this->default['password'] = rawurldecode($parts['pass']);
        }

        if (isset($parts['path'])) {
            $database = ltrim($parts['path'], '/');

            if ($database !== '') {
                $this->default['database'] = rawurldecode($database);
            }
        }

        if (isset($parts['port'])) {
            $this->default['port'] = (int) $parts['port'];
        }
    }

    private function setDefaultValue(string $configKey, string ...$envKeys): void
    {
        $value = $this->readEnvValue(...$envKeys);

        if ($value !== null) {
            $this->default[$configKey] = $value;
        }
    }

    private function readEnvValue(string ...$keys): ?string
    {
        foreach ($keys as $key) {
            $value = getenv($key);

            if ($value === false || $value === '') {
                $value = env($key);
            }

            if ($value !== null && $value !== false && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
