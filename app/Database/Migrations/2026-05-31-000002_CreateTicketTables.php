<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketTables extends Migration
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
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
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
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 10,
                'default'    => 0,
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
        $this->forge->addKey('parent_id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('parent_id', 'categories', 'id', 'CASCADE', 'SET NULL', 'fk_categories_parent_id');
        $this->forge->createTable('categories', true, $attributes);

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
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'level' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 3,
            ],
            'response_time_hours' => [
                'type'       => 'INT',
                'constraint' => 10,
                'default'    => 24,
            ],
            'resolution_time_hours' => [
                'type'       => 'INT',
                'constraint' => 10,
                'default'    => 72,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
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
        $this->forge->createTable('priorities', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'priority_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'response_time_minutes' => [
                'type'       => 'INT',
                'constraint' => 10,
                'default'    => 480,
            ],
            'resolution_time_minutes' => [
                'type'       => 'INT',
                'constraint' => 10,
                'default'    => 2880,
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
        $this->forge->addKey('category_id');
        $this->forge->addKey('priority_id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL', 'fk_sla_rules_category_id');
        $this->forge->addForeignKey('priority_id', 'priorities', 'id', 'CASCADE', 'SET NULL', 'fk_sla_rules_priority_id');
        $this->forge->createTable('sla_rules', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ticket_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'requester_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'assignee_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'priority_id' => [
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
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'open',
            ],
            'impact' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'medium',
            ],
            'urgency' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'medium',
            ],
            'sla_due_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'closed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('requester_id');
        $this->forge->addKey('assignee_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('priority_id');
        $this->forge->addKey('department_id');
        $this->forge->addKey('status');
        $this->forge->addUniqueKey('ticket_number');
        $this->forge->addForeignKey('requester_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_tickets_requester_id');
        $this->forge->addForeignKey('assignee_id', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_tickets_assignee_id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'SET NULL', 'fk_tickets_category_id');
        $this->forge->addForeignKey('priority_id', 'priorities', 'id', 'CASCADE', 'SET NULL', 'fk_tickets_priority_id');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'SET NULL', 'fk_tickets_department_id');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_tickets_created_by');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_tickets_updated_by');
        $this->forge->createTable('tickets', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ticket_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'comment' => [
                'type' => 'TEXT',
            ],
            'attachments' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_internal' => [
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'CASCADE', 'fk_ticket_comments_ticket_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'fk_ticket_comments_user_id');
        $this->forge->createTable('ticket_comments', true, $attributes);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ticket_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'changed_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'old_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'new_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->addKey('changed_by');
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'CASCADE', 'fk_ticket_status_logs_ticket_id');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_ticket_status_logs_changed_by');
        $this->forge->createTable('ticket_status_logs', true, $attributes);
    }

    public function down(): void
    {
        $this->forge->dropTable('ticket_status_logs', true);
        $this->forge->dropTable('ticket_comments', true);
        $this->forge->dropTable('tickets', true);
        $this->forge->dropTable('sla_rules', true);
        $this->forge->dropTable('priorities', true);
        $this->forge->dropTable('categories', true);
    }
}
