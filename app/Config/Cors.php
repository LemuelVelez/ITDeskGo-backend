<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    /**
     * The default CORS configuration.
     *
     * @var array{
     *      allowedOrigins: list<string>,
     *      allowedOriginsPatterns: list<string>,
     *      supportsCredentials: bool,
     *      allowedHeaders: list<string>,
     *      exposedHeaders: list<string>,
     *      allowedMethods: list<string>,
     *      maxAge: int,
     *  }
     */
    public array $default = [
        /**
         * Origins for the `Access-Control-Allow-Origin` header.
         *
         * Native Expo Android/iOS requests do not require browser CORS, but Expo Web
         * and local browser tools do. Keep local, Android emulator, and LAN origins
         * available for development.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
         */
        'allowedOrigins' => [
            'http://localhost:3000',
            'http://localhost:8080',
            'http://localhost:8081',
            'http://localhost:19006',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:8080',
            'http://127.0.0.1:8081',
            'http://127.0.0.1:19006',
            'http://10.0.2.2:8080',
            'http://10.0.2.2:8081',
            'http://10.0.2.2:19006',
        ],

        /**
         * Origin regex patterns for the `Access-Control-Allow-Origin` header.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
         *
         * NOTE: A pattern specified here is part of a regular expression. It will
         *       be actually `#\A<pattern>\z#`.
         */
        'allowedOriginsPatterns' => [
            'https?://localhost(:[0-9]+)?',
            'https?://127\.0\.0\.1(:[0-9]+)?',
            'https?://10\.0\.2\.2(:[0-9]+)?',
            'https?://192\.168\.[0-9]{1,3}\.[0-9]{1,3}(:[0-9]+)?',
            'https?://10\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(:[0-9]+)?',
            'https?://172\.(1[6-9]|2[0-9]|3[0-1])\.[0-9]{1,3}\.[0-9]{1,3}(:[0-9]+)?',
        ],

        /**
         * Whether to send the `Access-Control-Allow-Credentials` header.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials
         */
        'supportsCredentials' => false,

        /**
         * Set headers to allow.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers
         */
        'allowedHeaders' => [
            'Accept',
            'Authorization',
            'Content-Type',
            'Origin',
            'X-Requested-With',
            'X-User-Id',
        ],

        /**
         * Set headers to expose.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers
         */
        'exposedHeaders' => [
            'Authorization',
        ],

        /**
         * Set methods to allow.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods
         */
        'allowedMethods' => [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'OPTIONS',
        ],

        /**
         * Set how many seconds the results of a preflight request can be cached.
         *
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age
         */
        'maxAge' => 7200,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->default['allowedOrigins'] = $this->envList('CORS_ALLOWED_ORIGINS', $this->default['allowedOrigins']);
        $this->default['allowedOriginsPatterns'] = $this->envList('CORS_ALLOWED_ORIGIN_PATTERNS', $this->default['allowedOriginsPatterns']);
    }

    /**
     * @param list<string> $fallback
     *
     * @return list<string>
     */
    private function envList(string $key, array $fallback): array
    {
        $value = env($key);

        if (! is_string($value) || trim($value) === '') {
            return $fallback;
        }

        $items = array_values(array_unique(array_filter(array_map(
            static fn (string $item): string => trim($item, " \t\n\r\0\x0B'\""),
            explode(',', $value),
        ))));

        return $items === [] ? $fallback : $items;
    }
}
