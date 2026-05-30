<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class AuthController extends BaseApiController
{
    public function splash()
    {
        return $this->success([
            'app' => [
                'name'        => 'ITDeskGo',
                'description' => 'IT Helpdesk, Knowledge Base, and Asset Tracker',
                'version'     => '1.0.0',
            ],
            'auth_stack' => [
                'Splash Screen',
                'Login Screen',
                'Forgot Password Screen',
            ],
            'tabs' => [
                SchemaModel::ROLE_EMPLOYEE => ['Home', 'Tickets', 'Knowledge Base', 'Assets', 'Profile'],
                SchemaModel::ROLE_IT_STAFF => ['Dashboard', 'Tickets', 'Assets', 'Knowledge Base', 'Profile'],
                SchemaModel::ROLE_ADMIN    => ['Dashboard', 'Users', 'Tickets', 'Assets', 'Settings'],
            ],
            'roles' => SchemaModel::roles(),
        ]);
    }

    public function login()
    {
        $payload = $this->payload(['email', 'password']);
        $errors  = $this->requireFields($payload, ['email', 'password']);

        if ($errors !== []) {
            return $this->badRequest('Please provide your email and password.', $errors);
        }

        $user = $this->db->table(SchemaModel::TABLE_USERS)
            ->where('email', strtolower(trim((string) $payload['email'])))
            ->get()
            ->getRowArray();

        if (! is_array($user)) {
            return $this->badRequest('Invalid email or password.');
        }

        if (($user['status'] ?? SchemaModel::USER_STATUS_ACTIVE) !== SchemaModel::USER_STATUS_ACTIVE) {
            return $this->forbidden('Your account is not active.');
        }

        $passwordHash = $user['password_hash'] ?? $user['password'] ?? '';

        if (! is_string($passwordHash) || ! password_verify((string) $payload['password'], $passwordHash)) {
            return $this->badRequest('Invalid email or password.');
        }

        $role = null;

        if (! empty($user['role_id'])) {
            $role = $this->rowById(SchemaModel::TABLE_ROLES, (int) $user['role_id']);
        }

        $roleSlug = (string) ($role['slug'] ?? $role['name'] ?? SchemaModel::ROLE_EMPLOYEE);
        $roleSlug = strtolower(str_replace(' ', '_', $roleSlug));

        return $this->success([
            'user'  => $this->publicUser($user),
            'role'  => $role,
            'tabs'  => $this->tabsForRole($roleSlug),
            'token' => bin2hex(random_bytes(32)),
        ], 'Login successful.');
    }

    public function forgotPassword()
    {
        $payload = $this->payload(['email']);
        $errors  = $this->requireFields($payload, ['email']);

        if ($errors !== []) {
            return $this->badRequest('Please provide your email address.', $errors);
        }

        $email = strtolower(trim((string) $payload['email']));
        $user  = $this->db->table(SchemaModel::TABLE_USERS)->where('email', $email)->get()->getRowArray();

        if (is_array($user) && $this->tableHasColumn(SchemaModel::TABLE_USERS, 'reset_token')) {
            $resetToken = bin2hex(random_bytes(32));

            $this->db->table(SchemaModel::TABLE_USERS)
                ->where('id', (int) $user['id'])
                ->update($this->withTimestamps(SchemaModel::TABLE_USERS, [
                    'reset_token' => password_hash($resetToken, PASSWORD_DEFAULT),
                ]));
        }

        return $this->success([
            'email' => $email,
        ], 'If the email exists, password reset instructions will be prepared.');
    }

    public function profile(int $id)
    {
        $user = $this->rowById(SchemaModel::TABLE_USERS, $id);

        if ($user === null) {
            return $this->notFound('User not found.');
        }

        return $this->success([
            'user' => $this->publicUser($user),
        ]);
    }

    public function updateProfile(int $id)
    {
        $payload = $this->payload([
            'name',
            'first_name',
            'last_name',
            'phone',
            'avatar',
            'department_id',
        ]);

        $payload = $this->onlyExistingColumns(SchemaModel::TABLE_USERS, $payload);
        $payload = $this->withTimestamps(SchemaModel::TABLE_USERS, $payload);

        if ($payload === []) {
            return $this->badRequest('No valid profile fields were provided.');
        }

        if ($this->rowById(SchemaModel::TABLE_USERS, $id) === null) {
            return $this->notFound('User not found.');
        }

        $this->db->table(SchemaModel::TABLE_USERS)->where('id', $id)->update($payload);

        return $this->success([
            'user' => $this->publicUser($this->rowById(SchemaModel::TABLE_USERS, $id) ?? []),
        ], 'Profile updated successfully.');
    }

    public function changePassword(int $id)
    {
        $payload = $this->payload(['current_password', 'new_password']);
        $errors  = $this->requireFields($payload, ['current_password', 'new_password']);

        if ($errors !== []) {
            return $this->badRequest('Please complete the password fields.', $errors);
        }

        if (strlen((string) $payload['new_password']) < 8) {
            return $this->badRequest('New password must be at least 8 characters long.');
        }

        $user = $this->rowById(SchemaModel::TABLE_USERS, $id);

        if ($user === null) {
            return $this->notFound('User not found.');
        }

        $passwordHash = $user['password_hash'] ?? $user['password'] ?? '';

        if (! is_string($passwordHash) || ! password_verify((string) $payload['current_password'], $passwordHash)) {
            return $this->badRequest('Current password is incorrect.');
        }

        $passwordColumn = $this->tableHasColumn(SchemaModel::TABLE_USERS, 'password_hash') ? 'password_hash' : 'password';

        $this->db->table(SchemaModel::TABLE_USERS)
            ->where('id', $id)
            ->update($this->withTimestamps(SchemaModel::TABLE_USERS, [
                $passwordColumn => password_hash((string) $payload['new_password'], PASSWORD_DEFAULT),
            ]));

        return $this->success([], 'Password changed successfully.');
    }

    private function tabsForRole(string $role): array
    {
        return match ($role) {
            SchemaModel::ROLE_ADMIN, SchemaModel::ROLE_SUPERADMIN => ['Dashboard', 'Users', 'Tickets', 'Assets', 'Settings'],
            SchemaModel::ROLE_IT_STAFF => ['Dashboard', 'Tickets', 'Assets', 'Knowledge Base', 'Profile'],
            default => ['Home', 'Tickets', 'Knowledge Base', 'Assets', 'Profile'],
        };
    }
}
