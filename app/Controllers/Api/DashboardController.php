<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class DashboardController extends BaseApiController
{
    public function employeeHome(?int $userId = null)
    {
        $userId ??= $this->currentUserId();

        if ($userId === null) {
            return $this->badRequest('User ID is required.');
        }

        return $this->success([
            'tickets' => [
                'total'       => $this->count(SchemaModel::TABLE_TICKETS, ['requester_id' => $userId]),
                'open'        => $this->count(SchemaModel::TABLE_TICKETS, ['requester_id' => $userId, 'status' => SchemaModel::TICKET_STATUS_OPEN]),
                'in_progress' => $this->count(SchemaModel::TABLE_TICKETS, ['requester_id' => $userId, 'status' => SchemaModel::TICKET_STATUS_IN_PROGRESS]),
                'resolved'    => $this->count(SchemaModel::TABLE_TICKETS, ['requester_id' => $userId, 'status' => SchemaModel::TICKET_STATUS_RESOLVED]),
            ],
            'assets' => [
                'assigned' => $this->count(SchemaModel::TABLE_ASSET_ASSIGNMENTS, ['user_id' => $userId, 'returned_at' => null]),
            ],
            'knowledge_base' => [
                'published' => $this->count(SchemaModel::TABLE_KB_ARTICLES, ['status' => SchemaModel::KB_STATUS_PUBLISHED]),
            ],
        ]);
    }

    public function staffDashboard(?int $staffId = null)
    {
        $staffId ??= $this->currentUserId();

        $ticketBuilder = $this->db->table(SchemaModel::TABLE_TICKETS);

        if ($staffId !== null && $this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'assignee_id')) {
            $ticketBuilder->where('assignee_id', $staffId);
        }

        $assignedTickets = (int) $ticketBuilder->countAllResults();

        return $this->success([
            'tickets' => [
                'assigned_to_me' => $assignedTickets,
                'open'           => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_OPEN]),
                'in_progress'    => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_IN_PROGRESS]),
                'on_hold'        => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_ON_HOLD]),
                'resolved_today' => $this->countResolvedToday(),
            ],
            'assets' => [
                'available'   => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_AVAILABLE]),
                'assigned'    => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_ASSIGNED]),
                'maintenance' => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_MAINTENANCE]),
            ],
            'knowledge_base' => [
                'published' => $this->count(SchemaModel::TABLE_KB_ARTICLES, ['status' => SchemaModel::KB_STATUS_PUBLISHED]),
                'drafts'    => $this->count(SchemaModel::TABLE_KB_ARTICLES, ['status' => SchemaModel::KB_STATUS_DRAFT]),
            ],
        ]);
    }

    public function adminDashboard()
    {
        return $this->success([
            'users' => [
                'total'      => $this->count(SchemaModel::TABLE_USERS),
                'active'     => $this->count(SchemaModel::TABLE_USERS, ['status' => SchemaModel::USER_STATUS_ACTIVE]),
                'inactive'   => $this->count(SchemaModel::TABLE_USERS, ['status' => SchemaModel::USER_STATUS_INACTIVE]),
                'suspended'  => $this->count(SchemaModel::TABLE_USERS, ['status' => SchemaModel::USER_STATUS_SUSPENDED]),
            ],
            'tickets' => [
                'total'       => $this->count(SchemaModel::TABLE_TICKETS),
                'open'        => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_OPEN]),
                'in_progress' => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_IN_PROGRESS]),
                'resolved'    => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_RESOLVED]),
                'closed'      => $this->count(SchemaModel::TABLE_TICKETS, ['status' => SchemaModel::TICKET_STATUS_CLOSED]),
            ],
            'assets' => [
                'total'       => $this->count(SchemaModel::TABLE_ASSETS),
                'available'   => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_AVAILABLE]),
                'assigned'    => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_ASSIGNED]),
                'maintenance' => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_MAINTENANCE]),
                'retired'     => $this->count(SchemaModel::TABLE_ASSETS, ['status' => SchemaModel::ASSET_STATUS_RETIRED]),
            ],
            'knowledge_base' => [
                'total'     => $this->count(SchemaModel::TABLE_KB_ARTICLES),
                'published' => $this->count(SchemaModel::TABLE_KB_ARTICLES, ['status' => SchemaModel::KB_STATUS_PUBLISHED]),
                'drafts'    => $this->count(SchemaModel::TABLE_KB_ARTICLES, ['status' => SchemaModel::KB_STATUS_DRAFT]),
            ],
        ]);
    }

    public function reports()
    {
        return $this->success([
            'ticket_status_summary' => $this->groupCount(SchemaModel::TABLE_TICKETS, 'status'),
            'asset_status_summary'  => $this->groupCount(SchemaModel::TABLE_ASSETS, 'status'),
            'tickets_by_category'   => $this->groupCount(SchemaModel::TABLE_TICKETS, 'category_id'),
            'assets_by_type'        => $this->groupCount(SchemaModel::TABLE_ASSETS, 'asset_type_id'),
        ]);
    }

    private function count(string $table, array $where = []): int
    {
        $builder = $this->db->table($table);

        foreach ($where as $column => $value) {
            if ($value === null) {
                $builder->where($column, null);
                continue;
            }

            $builder->where($column, $value);
        }

        return (int) $builder->countAllResults();
    }

    private function countResolvedToday(): int
    {
        $builder = $this->db->table(SchemaModel::TABLE_TICKETS)
            ->where('status', SchemaModel::TICKET_STATUS_RESOLVED);

        if ($this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'resolved_at')) {
            $builder->where('DATE(resolved_at)', date('Y-m-d'), false);
        } elseif ($this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'updated_at')) {
            $builder->where('DATE(updated_at)', date('Y-m-d'), false);
        }

        return (int) $builder->countAllResults();
    }

    private function groupCount(string $table, string $column): array
    {
        if (! $this->tableHasColumn($table, $column)) {
            return [];
        }

        return $this->db->table($table)
            ->select($column . ' AS label, COUNT(*) AS total')
            ->groupBy($column)
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }
}
