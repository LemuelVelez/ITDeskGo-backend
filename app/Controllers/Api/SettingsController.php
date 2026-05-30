<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class SettingsController extends BaseApiController
{
    public function index()
    {
        return $this->success([
            'roles'          => SchemaModel::roles(),
            'user_statuses'  => SchemaModel::userStatuses(),
            'ticket_statuses'=> SchemaModel::ticketStatuses(),
            'kb_statuses'    => SchemaModel::kbStatuses(),
            'asset_statuses' => SchemaModel::assetStatuses(),
            'tables'         => SchemaModel::tables(),
        ]);
    }

    public function ticketOptions()
    {
        return $this->success([
            'categories'      => $this->db->table(SchemaModel::TABLE_CATEGORIES)->orderBy('name', 'ASC')->get()->getResultArray(),
            'priorities'      => $this->db->table(SchemaModel::TABLE_PRIORITIES)->orderBy('id', 'ASC')->get()->getResultArray(),
            'sla_rules'       => $this->db->table(SchemaModel::TABLE_SLA_RULES)->orderBy('id', 'ASC')->get()->getResultArray(),
            'ticket_statuses' => SchemaModel::ticketStatuses(),
        ]);
    }

    public function assetOptions()
    {
        return $this->success([
            'asset_types'    => $this->db->table(SchemaModel::TABLE_ASSET_TYPES)->orderBy('name', 'ASC')->get()->getResultArray(),
            'asset_statuses' => SchemaModel::assetStatuses(),
        ]);
    }

    public function knowledgeBaseOptions()
    {
        return $this->success([
            'categories'  => $this->db->table(SchemaModel::TABLE_KB_CATEGORIES)->orderBy('name', 'ASC')->get()->getResultArray(),
            'kb_statuses' => SchemaModel::kbStatuses(),
        ]);
    }

    public function health()
    {
        $tables = [];

        foreach (SchemaModel::tables() as $group => $groupTables) {
            foreach ($groupTables as $table) {
                $tables[$group][$table] = $this->db->tableExists($table);
            }
        }

        return $this->success([
            'database' => 'connected',
            'tables'   => $tables,
        ], 'System health checked successfully.');
    }
}
