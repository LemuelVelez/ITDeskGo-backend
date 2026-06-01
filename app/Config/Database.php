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
        'DBPrefix'    => 'db_',
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

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }

    private function applyRuntimeDatabaseConfig(): void
    {
        $this->applyDatabaseUrlConfig();

        $this->setDefaultValue('DBDriver', 'DB_DRIVER', 'DATABASE_DEFAULT_DBDRIVER', 'database.default.DBDriver');
        $this->setDefaultValue('hostname', 'DB_HOST', 'DB_HOSTNAME', 'MYSQL_HOST', 'MYSQLHOST', 'MARIADB_HOST', 'DATABASE_HOST', 'DATABASE_DEFAULT_HOSTNAME', 'database.default.hostname');
        $this->setDefaultValue('database', 'DB_NAME', 'DB_DATABASE', 'MYSQL_DATABASE', 'MYSQLDATABASE', 'MARIADB_DATABASE', 'DATABASE_NAME', 'DATABASE_DEFAULT_DATABASE', 'database.default.database');
        $this->setDefaultValue('username', 'DB_USER', 'DB_USERNAME', 'MYSQL_USER', 'MYSQLUSER', 'MARIADB_USER', 'DATABASE_USER', 'DATABASE_USERNAME', 'DATABASE_DEFAULT_USERNAME', 'database.default.username');
        $this->setDefaultValue('password', 'DB_PASS', 'DB_PASSWORD', 'MYSQL_PASSWORD', 'MYSQLPASSWORD', 'MARIADB_PASSWORD', 'DATABASE_PASSWORD', 'DATABASE_DEFAULT_PASSWORD', 'database.default.password');

        $port = $this->readEnvValue('DB_PORT', 'MYSQL_PORT', 'MYSQLPORT', 'MARIADB_PORT', 'DATABASE_PORT', 'DATABASE_DEFAULT_PORT', 'database.default.port');

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
        $databaseUrl = $this->readEnvValue('MySQL_DATABASE_URL', 'MYSQL_DATABASE_URL', 'MARIADB_DATABASE_URL', 'DATABASE_URL');

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
            $this->default['hostname'] = rawurldecode($parts['host']);
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
                return trim((string) $value, " \t\n\r\0\x0B\"'");
            }
        }

        return null;
    }
}
