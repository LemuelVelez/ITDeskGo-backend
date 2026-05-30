<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class UsersController extends BaseApiController
{
    public function index()
    {
        $pagination = $this->pagination();
        $builder    = $this->db->table(SchemaModel::TABLE_USERS . ' u')
            ->select('u.*')
            ->orderBy('u.id', 'DESC');

        if ($this->tableHasColumn(SchemaModel::TABLE_USERS, 'role_id')) {
            $builder->select('r.name AS role_name, r.slug AS role_slug')->join(SchemaModel::TABLE_ROLES . ' r', 'r.id = u.role_id', 'left');
        }

        if ($this->tableHasColumn(SchemaModel::TABLE_USERS, 'department_id')) {
            $builder->select('d.name AS department_name')->join(SchemaModel::TABLE_DEPARTMENTS . ' d', 'd.id = u.department_id', 'left');
        }

        $status = $this->request->getGet('status');
        if ($status !== null && $status !== '') {
            $builder->where('u.status', $status);
        }

        $roleId = $this->request->getGet('role_id');
        if ($roleId !== null && $roleId !== '') {
            $builder->where('u.role_id', $roleId);
        }

        $departmentId = $this->request->getGet('department_id');
        if ($departmentId !== null && $departmentId !== '') {
            $builder->where('u.department_id', $departmentId);
        }

        $this->applySearch($builder, $this->request->getGet('search'), ['u.name', 'u.email']);

        $result = $this->paginateBuilder($builder, $pagination);
        $result['items'] = array_map(fn (array $user): array => $this->publicUser($user), $result['items']);

        return $this->success($result);
    }

    public function show(int $id)
    {
        $user = $this->rowById(SchemaModel::TABLE_USERS, $id);

        if ($user === null) {
            return $this->notFound('User not found.');
        }

        return $this->success([
            'user' => $this->publicUser($user),
        ]);
    }

    public function create()
    {
        $payload = $this->payload([
            'name',
            'email',
            'password',
            'role_id',
            'department_id',
            'status',
            'phone',
        ]);

        $errors = $this->requireFields($payload, ['name', 'email', 'password']);

        if ($errors !== []) {
            return $this->badRequest('Please complete the required user fields.', $errors);
        }

        $email = strtolower(trim((string) $payload['email']));
        $exists = $this->db->table(SchemaModel::TABLE_USERS)->where('email', $email)->countAllResults() > 0;

        if ($exists) {
            return $this->badRequest('Email address is already registered.');
        }

        $passwordColumn = $this->tableHasColumn(SchemaModel::TABLE_USERS, 'password_hash') ? 'password_hash' : 'password';

        $insert = [
            'name'           => trim((string) $payload['name']),
            'email'          => $email,
            $passwordColumn  => password_hash((string) $payload['password'], PASSWORD_DEFAULT),
            'status'         => $payload['status'] ?? SchemaModel::USER_STATUS_ACTIVE,
            'role_id'        => $payload['role_id'] ?? null,
            'department_id'  => $payload['department_id'] ?? null,
            'phone'          => $payload['phone'] ?? null,
        ];

        $insert = $this->withTimestamps(SchemaModel::TABLE_USERS, $this->onlyExistingColumns(SchemaModel::TABLE_USERS, $insert), true);

        $this->db->table(SchemaModel::TABLE_USERS)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'user' => $this->publicUser($this->rowById(SchemaModel::TABLE_USERS, $id) ?? []),
        ], 'User created successfully.');
    }

    public function update(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_USERS, $id) === null) {
            return $this->notFound('User not found.');
        }

        $payload = $this->payload([
            'name',
            'email',
            'password',
            'role_id',
            'department_id',
            'status',
            'phone',
        ]);

        if (isset($payload['email'])) {
            $payload['email'] = strtolower(trim((string) $payload['email']));

            $exists = $this->db->table(SchemaModel::TABLE_USERS)
                ->where('email', $payload['email'])
                ->where('id !=', $id)
                ->countAllResults() > 0;

            if ($exists) {
                return $this->badRequest('Email address is already registered.');
            }
        }

        if (isset($payload['password'])) {
            $passwordColumn = $this->tableHasColumn(SchemaModel::TABLE_USERS, 'password_hash') ? 'password_hash' : 'password';
            $payload[$passwordColumn] = password_hash((string) $payload['password'], PASSWORD_DEFAULT);
            unset($payload['password']);
        }

        $payload = $this->withTimestamps(SchemaModel::TABLE_USERS, $this->onlyExistingColumns(SchemaModel::TABLE_USERS, $payload));

        if ($payload === []) {
            return $this->badRequest('No valid user fields were provided.');
        }

        $this->db->table(SchemaModel::TABLE_USERS)->where('id', $id)->update($payload);

        return $this->success([
            'user' => $this->publicUser($this->rowById(SchemaModel::TABLE_USERS, $id) ?? []),
        ], 'User updated successfully.');
    }

    public function delete(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_USERS, $id) === null) {
            return $this->notFound('User not found.');
        }

        $this->db->table(SchemaModel::TABLE_USERS)->where('id', $id)->delete();

        return $this->success([], 'User deleted successfully.');
    }

    public function roles()
    {
        return $this->success([
            'roles' => $this->db->table(SchemaModel::TABLE_ROLES)->orderBy('id', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function departments()
    {
        return $this->success([
            'departments' => $this->db->table(SchemaModel::TABLE_DEPARTMENTS)->orderBy('name', 'ASC')->get()->getResultArray(),
        ]);
    }
}
