<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKnowledgeBaseTables extends Migration
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
        $this->forge->addForeignKey('parent_id', 'kb_categories', 'id', 'CASCADE', 'SET NULL', 'fk_kb_categories_parent_id');
        $this->forge->createTable('kb_categories', true, $attributes);

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
            'author_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 220,
            ],
            'excerpt' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'content' => [
                'type' => 'LONGTEXT',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'draft',
            ],
            'visibility' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'employee',
            ],
            'views_count' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'published_at' => [
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
        $this->forge->addKey('category_id');
        $this->forge->addKey('author_id');
        $this->forge->addKey('status');
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('category_id', 'kb_categories', 'id', 'CASCADE', 'SET NULL', 'fk_kb_articles_category_id');
        $this->forge->addForeignKey('author_id', 'users', 'id', 'CASCADE', 'SET NULL', 'fk_kb_articles_author_id');
        $this->forge->createTable('kb_articles', true, $attributes);
    }

    public function down(): void
    {
        $this->forge->dropTable('kb_articles', true);
        $this->forge->dropTable('kb_categories', true);
    }
}
