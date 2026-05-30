<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->upsertByKey('roles', 'key', [
            [
                'name'        => 'Super Admin',
                'key'         => 'superadmin',
                'description' => 'Full system access including all admin settings.',
                'is_system'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Admin',
                'key'         => 'admin',
                'description' => 'Manages users, tickets, assets, and settings.',
                'is_system'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'IT Staff',
                'key'         => 'it_staff',
                'description' => 'Handles assigned tickets, assets, and knowledge-base content.',
                'is_system'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Employee',
                'key'         => 'employee',
                'description' => 'Creates tickets and views assigned assets and knowledge-base articles.',
                'is_system'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        $this->upsertByKey('departments', 'code', [
            [
                'name'        => 'Information Technology',
                'code'        => 'IT',
                'description' => 'IT support and administration department.',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'General Administration',
                'code'        => 'ADMIN',
                'description' => 'Administrative users and employees.',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        $this->upsertByKey('priorities', 'slug', [
            [
                'name'                  => 'Low',
                'slug'                  => 'low',
                'level'                 => 4,
                'response_time_hours'   => 24,
                'resolution_time_hours' => 120,
                'color'                 => 'gray',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'Medium',
                'slug'                  => 'medium',
                'level'                 => 3,
                'response_time_hours'   => 8,
                'resolution_time_hours' => 72,
                'color'                 => 'blue',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'High',
                'slug'                  => 'high',
                'level'                 => 2,
                'response_time_hours'   => 4,
                'resolution_time_hours' => 24,
                'color'                 => 'orange',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'name'                  => 'Critical',
                'slug'                  => 'critical',
                'level'                 => 1,
                'response_time_hours'   => 1,
                'resolution_time_hours' => 8,
                'color'                 => 'red',
                'is_active'             => 1,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
        ]);

        $this->upsertByKey('categories', 'slug', [
            [
                'name'        => 'Hardware',
                'slug'        => 'hardware',
                'description' => 'Desktop, laptop, printer, and peripheral issues.',
                'sort_order'  => 1,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Software',
                'slug'        => 'software',
                'description' => 'Application installation, access, and troubleshooting.',
                'sort_order'  => 2,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Network',
                'slug'        => 'network',
                'description' => 'Internet, Wi-Fi, LAN, and connectivity concerns.',
                'sort_order'  => 3,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Account Access',
                'slug'        => 'account-access',
                'description' => 'Login, password, email, and account permission requests.',
                'sort_order'  => 4,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        $this->upsertByKey('kb_categories', 'slug', [
            [
                'name'        => 'Getting Started',
                'slug'        => 'getting-started',
                'description' => 'Basic guides for using ITDeskGo.',
                'sort_order'  => 1,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Troubleshooting',
                'slug'        => 'troubleshooting',
                'description' => 'Common fixes for recurring IT issues.',
                'sort_order'  => 2,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);

        $this->upsertByKey('asset_types', 'slug', [
            [
                'name'        => 'Laptop',
                'slug'        => 'laptop',
                'description' => 'Portable computers assigned to users.',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Desktop',
                'slug'        => 'desktop',
                'description' => 'Desktop workstations and computer units.',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Printer',
                'slug'        => 'printer',
                'description' => 'Printers and scanners tracked by IT.',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Network Device',
                'slug'        => 'network-device',
                'description' => 'Routers, switches, access points, and related equipment.',
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    private function upsertByKey(string $table, string $key, array $rows): void
    {
        foreach ($rows as $row) {
            $existing = $this->db->table($table)->where($key, $row[$key])->get()->getRowArray();

            if ($existing === null) {
                $this->db->table($table)->insert($row);
                continue;
            }

            unset($row['created_at']);
            $this->db->table($table)->where($key, $existing[$key])->update($row);
        }
    }
}
