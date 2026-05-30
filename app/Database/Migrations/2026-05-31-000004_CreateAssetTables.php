<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetTables extends Migration
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
                'constraint' => 120,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 140,
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
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('asset_types', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asset_type_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assigned_to' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'asset_tag' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'brand' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'serial_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'available',
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
            ],
            'purchase_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'warranty_expiry' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('asset_type_id');
        $this->forge->addKey('assigned_to');
        $this->forge->addKey('department_id');
        $this->forge->addKey('status');
        $this->forge->addUniqueKey('asset_tag');
        $this->forge->addUniqueKey('serial_number');
        $this->forge->addForeignKey('asset_type_id', 'asset_types', 'id', 'CASCADE', 'SET NULL', 'fk_assets_asset_type_id');
        $this->forge->addForeignKey('assigned_to', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_assets_assigned_to');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'SET NULL', 'fk_assets_department_id');
        $this->forge->createTable('assets', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asset_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'assigned_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'returned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'condition_on_assign' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'condition_on_return' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('assigned_by');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'CASCADE', 'CASCADE', 'fk_asset_assignments_asset_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_asset_assignments_user_id');
        $this->forge->addForeignKey('assigned_by', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_asset_assignments_assigned_by');
        $this->forge->createTable('asset_assignments', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asset_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'reported_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'performed_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'maintenance_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'next_maintenance_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'completed',
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
        $this->forge->addKey('asset_id');
        $this->forge->addKey('reported_by');
        $this->forge->addKey('performed_by');
        $this->forge->addForeignKey('asset_id', 'assets', 'id', 'CASCADE', 'CASCADE', 'fk_asset_maintenance_logs_asset_id');
        $this->forge->addForeignKey('reported_by', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_asset_maintenance_logs_reported_by');
        $this->forge->addForeignKey('performed_by', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_asset_maintenance_logs_performed_by');
        $this->forge->createTable('asset_maintenance_logs', true, $attributes);
    }

    public function down(): void
    {
        $this->forge->dropTable('asset_maintenance_logs', true);
        $this->forge->dropTable('asset_assignments', true);
        $this->forge->dropTable('assets', true);
        $this->forge->dropTable('asset_types', true);
    }
}
