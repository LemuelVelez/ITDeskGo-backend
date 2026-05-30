<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Config\Database;

abstract class BaseApiController extends ResourceController
{
    protected $format = 'json';

    protected \CodeIgniter\Database\BaseConnection $db;

    /** @var array<string, array<string, bool>> */
    private array $columnCache = [];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    protected function success(array $data = [], string $message = 'Success', int $status = 200)
    {
        return $this->respond([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function created(array $data = [], string $message = 'Created')
    {
        return $this->success($data, $message, 201);
    }

    protected function badRequest(string $message, array $errors = [])
    {
        return $this->respond([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], 400);
    }

    protected function notFound(string $message = 'Record not found')
    {
        return $this->respond([
            'status'  => 'error',
            'message' => $message,
        ], 404);
    }

    protected function forbidden(string $message = 'Forbidden')
    {
        return $this->respond([
            'status'  => 'error',
            'message' => $message,
        ], 403);
    }

    protected function payload(array $allowedFields = []): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload)) {
            $payload = $this->request->getPost();
        }

        if (! is_array($payload)) {
            $payload = [];
        }

        if ($allowedFields === []) {
            return $payload;
        }

        $filtered = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $payload)) {
                $filtered[$field] = $payload[$field];
            }
        }

        return $filtered;
    }

    protected function requireFields(array $payload, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        return $errors;
    }

    protected function pagination(): array
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $perPage = min(max(1, $perPage), 100);

        return [
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => ($page - 1) * $perPage,
        ];
    }

    protected function paginateBuilder($builder, array $pagination): array
    {
        $countBuilder = clone $builder;
        $total        = (int) $countBuilder->countAllResults(false);
        $rows         = $builder->limit($pagination['per_page'], $pagination['offset'])->get()->getResultArray();

        return [
            'items' => $rows,
            'meta'  => [
                'page'        => $pagination['page'],
                'per_page'    => $pagination['per_page'],
                'total'       => $total,
                'total_pages' => (int) ceil($total / $pagination['per_page']),
            ],
        ];
    }

    protected function applySearch($builder, ?string $search, array $fields): void
    {
        $search = trim((string) $search);

        if ($search === '' || $fields === []) {
            return;
        }

        $builder->groupStart();

        foreach ($fields as $index => $field) {
            if ($index === 0) {
                $builder->like($field, $search);
                continue;
            }

            $builder->orLike($field, $search);
        }

        $builder->groupEnd();
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        if (! isset($this->columnCache[$table])) {
            $this->columnCache[$table] = [];

            foreach ($this->db->getFieldNames($table) as $field) {
                $this->columnCache[$table][$field] = true;
            }
        }

        return isset($this->columnCache[$table][$column]);
    }

    protected function onlyExistingColumns(string $table, array $payload): array
    {
        $clean = [];

        foreach ($payload as $field => $value) {
            if ($this->tableHasColumn($table, (string) $field)) {
                $clean[$field] = $value;
            }
        }

        return $clean;
    }

    protected function withTimestamps(string $table, array $payload, bool $isCreate = false): array
    {
        $now = date('Y-m-d H:i:s');

        if ($isCreate && $this->tableHasColumn($table, 'created_at') && ! array_key_exists('created_at', $payload)) {
            $payload['created_at'] = $now;
        }

        if ($this->tableHasColumn($table, 'updated_at') && ! array_key_exists('updated_at', $payload)) {
            $payload['updated_at'] = $now;
        }

        return $payload;
    }

    protected function rowById(string $table, int $id, string $primaryKey = 'id'): ?array
    {
        $row = $this->db->table($table)->where($primaryKey, $id)->get()->getRowArray();

        return is_array($row) ? $row : null;
    }

    protected function currentUserId(): ?int
    {
        $headerId = $this->request->getHeaderLine('X-User-Id');

        if ($headerId !== '' && ctype_digit($headerId)) {
            return (int) $headerId;
        }

        $payload = $this->payload();

        if (isset($payload['user_id']) && is_numeric($payload['user_id'])) {
            return (int) $payload['user_id'];
        }

        return null;
    }

    protected function slug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : bin2hex(random_bytes(4));
    }

    protected function publicUser(array $user): array
    {
        unset($user['password'], $user['password_hash'], $user['reset_token'], $user['remember_token']);

        return $user;
    }
}
