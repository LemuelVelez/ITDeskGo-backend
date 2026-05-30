<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) env('SUPERADMIN_EMAIL', '')));
        $password = (string) env('SUPERADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            throw new RuntimeException('SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD are required before running SuperAdminSeeder.');
        }

        $now = date('Y-m-d H:i:s');

        $role = $this->db->table('roles')->where('key', 'superadmin')->get()->getRowArray();
        if ($role === null) {
            $this->db->table('roles')->insert([
                'name'        => 'Super Admin',
                'key'         => 'superadmin',
                'description' => 'Full system access including all admin settings.',
                'is_system'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $roleId = (int) $this->db->insertID();
        } else {
            $roleId = (int) $role['id'];
        }

        $payload = [
            'role_id'           => $roleId,
            'department_id'     => null,
            'employee_id'       => 'SUPERADMIN',
            'first_name'        => 'Super',
            'last_name'         => 'Admin',
            'name'              => 'Super Admin',
            'email'             => $email,
            'password_hash'     => password_hash($password, PASSWORD_DEFAULT),
            'phone'             => null,
            'avatar'            => null,
            'status'            => 'active',
            'email_verified_at' => $now,
            'updated_at'        => $now,
        ];

        $existing = $this->db->table('users')->where('email', $email)->get()->getRowArray();

        if ($existing === null) {
            $payload['created_at'] = $now;
            $this->db->table('users')->insert($payload);
            return;
        }

        $this->db->table('users')->where('id', $existing['id'])->update($payload);
    }
}
