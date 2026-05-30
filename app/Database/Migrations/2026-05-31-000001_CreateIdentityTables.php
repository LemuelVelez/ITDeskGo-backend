<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIdentityTables extends Migration
{
    public function up(): void
    {
        $attributes = ['ENGINE' => 'InnoDB'];

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_system' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('roles', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('departments', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'employee_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 170,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'active',
            ],
            'password_reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'password_reset_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('role_id');
        $this->forge->addKey('department_id');
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('employee_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'RESTRICT', 'fk_users_role_id');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'SET NULL', 'fk_users_department_id');
        $this->forge->createTable('users', true, $attributes);
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('departments', true);
        $this->forge->dropTable('roles', true);
    }
}
