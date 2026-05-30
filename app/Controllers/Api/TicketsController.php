<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class TicketsController extends BaseApiController
{
    public function index()
    {
        $pagination = $this->pagination();
        $builder    = $this->db->table(SchemaModel::TABLE_TICKETS . ' t')
            ->select('t.*')
            ->orderBy('t.id', 'DESC');

        $this->joinTicketLookups($builder);

        foreach (['status', 'category_id', 'priority_id', 'requester_id', 'assignee_id'] as $filter) {
            $value = $this->request->getGet($filter);

            if ($value !== null && $value !== '' && $this->tableHasColumn(SchemaModel::TABLE_TICKETS, $filter)) {
                $builder->where('t.' . $filter, $value);
            }
        }

        $this->applySearch($builder, $this->request->getGet('search'), ['t.ticket_number', 't.subject', 't.description']);

        return $this->success($this->paginateBuilder($builder, $pagination));
    }

    public function show(int $id)
    {
        $ticket = $this->rowById(SchemaModel::TABLE_TICKETS, $id);

        if ($ticket === null) {
            return $this->notFound('Ticket not found.');
        }

        return $this->success([
            'ticket'   => $ticket,
            'comments' => $this->db->table(SchemaModel::TABLE_TICKET_COMMENTS)->where('ticket_id', $id)->orderBy('id', 'ASC')->get()->getResultArray(),
            'logs'     => $this->db->table(SchemaModel::TABLE_TICKET_STATUS_LOGS)->where('ticket_id', $id)->orderBy('id', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function create()
    {
        $payload = $this->payload([
            'ticket_number',
            'requester_id',
            'assignee_id',
            'category_id',
            'priority_id',
            'subject',
            'description',
            'status',
            'due_at',
        ]);

        $errors = $this->requireFields($payload, ['requester_id', 'subject', 'description']);

        if ($errors !== []) {
            return $this->badRequest('Please complete the required ticket fields.', $errors);
        }

        $payload['ticket_number'] = $payload['ticket_number'] ?? $this->ticketNumber();
        $payload['status']        = $payload['status'] ?? SchemaModel::TICKET_STATUS_OPEN;

        $insert = $this->withTimestamps(SchemaModel::TABLE_TICKETS, $this->onlyExistingColumns(SchemaModel::TABLE_TICKETS, $payload), true);

        $this->db->table(SchemaModel::TABLE_TICKETS)->insert($insert);
        $id = (int) $this->db->insertID();

        $this->writeStatusLog($id, null, (string) $insert['status'], 'Ticket created.');

        return $this->created([
            'ticket' => $this->rowById(SchemaModel::TABLE_TICKETS, $id),
        ], 'Ticket created successfully.');
    }

    public function update(int $id)
    {
        $ticket = $this->rowById(SchemaModel::TABLE_TICKETS, $id);

        if ($ticket === null) {
            return $this->notFound('Ticket not found.');
        }

        $payload = $this->payload([
            'requester_id',
            'assignee_id',
            'category_id',
            'priority_id',
            'subject',
            'description',
            'status',
            'due_at',
            'resolved_at',
        ]);

        $oldStatus = $ticket['status'] ?? null;
        $newStatus = $payload['status'] ?? $oldStatus;

        if ($newStatus === SchemaModel::TICKET_STATUS_RESOLVED && $this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'resolved_at') && empty($payload['resolved_at'])) {
            $payload['resolved_at'] = date('Y-m-d H:i:s');
        }

        $payload = $this->withTimestamps(SchemaModel::TABLE_TICKETS, $this->onlyExistingColumns(SchemaModel::TABLE_TICKETS, $payload));

        if ($payload === []) {
            return $this->badRequest('No valid ticket fields were provided.');
        }

        $this->db->table(SchemaModel::TABLE_TICKETS)->where('id', $id)->update($payload);

        if ($oldStatus !== $newStatus) {
            $this->writeStatusLog($id, is_string($oldStatus) ? $oldStatus : null, (string) $newStatus, 'Ticket status updated.');
        }

        return $this->success([
            'ticket' => $this->rowById(SchemaModel::TABLE_TICKETS, $id),
        ], 'Ticket updated successfully.');
    }

    public function delete(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_TICKETS, $id) === null) {
            return $this->notFound('Ticket not found.');
        }

        $this->db->table(SchemaModel::TABLE_TICKETS)->where('id', $id)->delete();

        return $this->success([], 'Ticket deleted successfully.');
    }

    public function addComment(int $ticketId)
    {
        if ($this->rowById(SchemaModel::TABLE_TICKETS, $ticketId) === null) {
            return $this->notFound('Ticket not found.');
        }

        $payload = $this->payload(['user_id', 'comment', 'body', 'is_internal']);
        $errors  = $this->requireFields($payload, ['user_id']);

        $commentText = $payload['comment'] ?? $payload['body'] ?? null;

        if ($commentText === null || trim((string) $commentText) === '') {
            $errors['comment'] = 'Comment is required.';
        }

        if ($errors !== []) {
            return $this->badRequest('Please complete the comment fields.', $errors);
        }

        $insert = [
            'ticket_id'   => $ticketId,
            'user_id'     => $payload['user_id'],
            'comment'     => $commentText,
            'body'        => $commentText,
            'is_internal' => $payload['is_internal'] ?? 0,
        ];

        $insert = $this->withTimestamps(SchemaModel::TABLE_TICKET_COMMENTS, $this->onlyExistingColumns(SchemaModel::TABLE_TICKET_COMMENTS, $insert), true);

        $this->db->table(SchemaModel::TABLE_TICKET_COMMENTS)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'comment' => $this->rowById(SchemaModel::TABLE_TICKET_COMMENTS, $id),
        ], 'Comment added successfully.');
    }

    public function changeStatus(int $ticketId)
    {
        $ticket = $this->rowById(SchemaModel::TABLE_TICKETS, $ticketId);

        if ($ticket === null) {
            return $this->notFound('Ticket not found.');
        }

        $payload = $this->payload(['status', 'remarks']);
        $errors  = $this->requireFields($payload, ['status']);

        if ($errors !== []) {
            return $this->badRequest('Please provide a ticket status.', $errors);
        }

        if (! in_array($payload['status'], SchemaModel::ticketStatuses(), true)) {
            return $this->badRequest('Invalid ticket status.');
        }

        $update = ['status' => $payload['status']];

        if ($payload['status'] === SchemaModel::TICKET_STATUS_RESOLVED && $this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'resolved_at')) {
            $update['resolved_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table(SchemaModel::TABLE_TICKETS)
            ->where('id', $ticketId)
            ->update($this->withTimestamps(SchemaModel::TABLE_TICKETS, $update));

        $this->writeStatusLog($ticketId, (string) ($ticket['status'] ?? ''), (string) $payload['status'], (string) ($payload['remarks'] ?? 'Ticket status changed.'));

        return $this->success([
            'ticket' => $this->rowById(SchemaModel::TABLE_TICKETS, $ticketId),
        ], 'Ticket status updated successfully.');
    }

    public function categories()
    {
        return $this->success([
            'categories' => $this->db->table(SchemaModel::TABLE_CATEGORIES)->orderBy('name', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function priorities()
    {
        return $this->success([
            'priorities' => $this->db->table(SchemaModel::TABLE_PRIORITIES)->orderBy('id', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function slaRules()
    {
        return $this->success([
            'sla_rules' => $this->db->table(SchemaModel::TABLE_SLA_RULES)->orderBy('id', 'ASC')->get()->getResultArray(),
        ]);
    }

    private function joinTicketLookups($builder): void
    {
        if ($this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'category_id')) {
            $builder->select('c.name AS category_name')->join(SchemaModel::TABLE_CATEGORIES . ' c', 'c.id = t.category_id', 'left');
        }

        if ($this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'priority_id')) {
            $builder->select('p.name AS priority_name')->join(SchemaModel::TABLE_PRIORITIES . ' p', 'p.id = t.priority_id', 'left');
        }

        if ($this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'requester_id')) {
            $builder->select('requester.name AS requester_name')->join(SchemaModel::TABLE_USERS . ' requester', 'requester.id = t.requester_id', 'left');
        }

        if ($this->tableHasColumn(SchemaModel::TABLE_TICKETS, 'assignee_id')) {
            $builder->select('assignee.name AS assignee_name')->join(SchemaModel::TABLE_USERS . ' assignee', 'assignee.id = t.assignee_id', 'left');
        }
    }

    private function ticketNumber(): string
    {
        return 'TKT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    private function writeStatusLog(int $ticketId, ?string $oldStatus, string $newStatus, string $remarks): void
    {
        $insert = [
            'ticket_id'  => $ticketId,
            'changed_by' => $this->currentUserId(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remarks'    => $remarks,
        ];

        $insert = $this->withTimestamps(SchemaModel::TABLE_TICKET_STATUS_LOGS, $this->onlyExistingColumns(SchemaModel::TABLE_TICKET_STATUS_LOGS, $insert), true);

        if ($insert !== []) {
            $this->db->table(SchemaModel::TABLE_TICKET_STATUS_LOGS)->insert($insert);
        }
    }
}
