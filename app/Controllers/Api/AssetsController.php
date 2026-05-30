<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class AssetsController extends BaseApiController
{
    public function index()
    {
        $pagination = $this->pagination();
        $builder    = $this->db->table(SchemaModel::TABLE_ASSETS . ' a')
            ->select('a.*')
            ->orderBy('a.id', 'DESC');

        if ($this->tableHasColumn(SchemaModel::TABLE_ASSETS, 'asset_type_id')) {
            $builder->select('at.name AS asset_type_name')->join(SchemaModel::TABLE_ASSET_TYPES . ' at', 'at.id = a.asset_type_id', 'left');
        }

        foreach (['status', 'asset_type_id', 'assigned_to'] as $filter) {
            $value = $this->request->getGet($filter);

            if ($value !== null && $value !== '' && $this->tableHasColumn(SchemaModel::TABLE_ASSETS, $filter)) {
                $builder->where('a.' . $filter, $value);
            }
        }

        $this->applySearch($builder, $this->request->getGet('search'), ['a.asset_tag', 'a.name', 'a.asset_name', 'a.serial_number']);

        return $this->success($this->paginateBuilder($builder, $pagination));
    }

    public function show(int $id)
    {
        $asset = $this->rowById(SchemaModel::TABLE_ASSETS, $id);

        if ($asset === null) {
            return $this->notFound('Asset not found.');
        }

        return $this->success([
            'asset'            => $asset,
            'assignments'      => $this->db->table(SchemaModel::TABLE_ASSET_ASSIGNMENTS)->where('asset_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(),
            'maintenance_logs' => $this->db->table(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS)->where('asset_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function create()
    {
        $payload = $this->payload([
            'asset_tag',
            'asset_type_id',
            'name',
            'asset_name',
            'serial_number',
            'brand',
            'model',
            'status',
            'assigned_to',
            'purchase_date',
            'purchase_cost',
            'location',
            'notes',
        ]);

        $displayName = $payload['name'] ?? $payload['asset_name'] ?? null;

        if ($displayName === null || trim((string) $displayName) === '') {
            return $this->badRequest('Asset name is required.', ['name' => 'Asset name is required.']);
        }

        $payload['status'] = $payload['status'] ?? SchemaModel::ASSET_STATUS_AVAILABLE;

        if (! isset($payload['name']) && $this->tableHasColumn(SchemaModel::TABLE_ASSETS, 'name')) {
            $payload['name'] = $displayName;
        }

        if (! isset($payload['asset_name']) && $this->tableHasColumn(SchemaModel::TABLE_ASSETS, 'asset_name')) {
            $payload['asset_name'] = $displayName;
        }

        $insert = $this->withTimestamps(SchemaModel::TABLE_ASSETS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSETS, $payload), true);

        $this->db->table(SchemaModel::TABLE_ASSETS)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'asset' => $this->rowById(SchemaModel::TABLE_ASSETS, $id),
        ], 'Asset created successfully.');
    }

    public function update(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_ASSETS, $id) === null) {
            return $this->notFound('Asset not found.');
        }

        $payload = $this->payload([
            'asset_tag',
            'asset_type_id',
            'name',
            'asset_name',
            'serial_number',
            'brand',
            'model',
            'status',
            'assigned_to',
            'purchase_date',
            'purchase_cost',
            'location',
            'notes',
        ]);

        $payload = $this->withTimestamps(SchemaModel::TABLE_ASSETS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSETS, $payload));

        if ($payload === []) {
            return $this->badRequest('No valid asset fields were provided.');
        }

        $this->db->table(SchemaModel::TABLE_ASSETS)->where('id', $id)->update($payload);

        return $this->success([
            'asset' => $this->rowById(SchemaModel::TABLE_ASSETS, $id),
        ], 'Asset updated successfully.');
    }

    public function delete(int $id)
    {
        if ($this->rowById(SchemaModel::TABLE_ASSETS, $id) === null) {
            return $this->notFound('Asset not found.');
        }

        $this->db->table(SchemaModel::TABLE_ASSETS)->where('id', $id)->delete();

        return $this->success([], 'Asset deleted successfully.');
    }

    public function types()
    {
        return $this->success([
            'asset_types' => $this->db->table(SchemaModel::TABLE_ASSET_TYPES)->orderBy('name', 'ASC')->get()->getResultArray(),
        ]);
    }

    public function createType()
    {
        $payload = $this->payload(['name', 'description']);
        $errors  = $this->requireFields($payload, ['name']);

        if ($errors !== []) {
            return $this->badRequest('Please provide an asset type name.', $errors);
        }

        $insert = $this->withTimestamps(SchemaModel::TABLE_ASSET_TYPES, $this->onlyExistingColumns(SchemaModel::TABLE_ASSET_TYPES, $payload), true);

        $this->db->table(SchemaModel::TABLE_ASSET_TYPES)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'asset_type' => $this->rowById(SchemaModel::TABLE_ASSET_TYPES, $id),
        ], 'Asset type created successfully.');
    }

    public function assign(int $assetId)
    {
        $asset = $this->rowById(SchemaModel::TABLE_ASSETS, $assetId);

        if ($asset === null) {
            return $this->notFound('Asset not found.');
        }

        $payload = $this->payload(['user_id', 'assigned_by', 'assigned_at', 'notes']);
        $errors  = $this->requireFields($payload, ['user_id']);

        if ($errors !== []) {
            return $this->badRequest('Please provide the assigned user.', $errors);
        }

        $insert = [
            'asset_id'    => $assetId,
            'user_id'     => $payload['user_id'],
            'assigned_by' => $payload['assigned_by'] ?? $this->currentUserId(),
            'assigned_at' => $payload['assigned_at'] ?? date('Y-m-d H:i:s'),
            'notes'       => $payload['notes'] ?? null,
        ];

        $insert = $this->withTimestamps(SchemaModel::TABLE_ASSET_ASSIGNMENTS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSET_ASSIGNMENTS, $insert), true);

        $this->db->table(SchemaModel::TABLE_ASSET_ASSIGNMENTS)->insert($insert);
        $assignmentId = (int) $this->db->insertID();

        $assetUpdate = ['status' => SchemaModel::ASSET_STATUS_ASSIGNED];

        if ($this->tableHasColumn(SchemaModel::TABLE_ASSETS, 'assigned_to')) {
            $assetUpdate['assigned_to'] = $payload['user_id'];
        }

        $this->db->table(SchemaModel::TABLE_ASSETS)
            ->where('id', $assetId)
            ->update($this->withTimestamps(SchemaModel::TABLE_ASSETS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSETS, $assetUpdate)));

        return $this->created([
            'assignment' => $this->rowById(SchemaModel::TABLE_ASSET_ASSIGNMENTS, $assignmentId),
            'asset'      => $this->rowById(SchemaModel::TABLE_ASSETS, $assetId),
        ], 'Asset assigned successfully.');
    }

    public function returnAssignment(int $assignmentId)
    {
        $assignment = $this->rowById(SchemaModel::TABLE_ASSET_ASSIGNMENTS, $assignmentId);

        if ($assignment === null) {
            return $this->notFound('Asset assignment not found.');
        }

        $assignmentUpdate = $this->withTimestamps(SchemaModel::TABLE_ASSET_ASSIGNMENTS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSET_ASSIGNMENTS, [
            'returned_at' => date('Y-m-d H:i:s'),
        ]));

        $this->db->table(SchemaModel::TABLE_ASSET_ASSIGNMENTS)->where('id', $assignmentId)->update($assignmentUpdate);

        $assetUpdate = ['status' => SchemaModel::ASSET_STATUS_AVAILABLE];

        if ($this->tableHasColumn(SchemaModel::TABLE_ASSETS, 'assigned_to')) {
            $assetUpdate['assigned_to'] = null;
        }

        $this->db->table(SchemaModel::TABLE_ASSETS)
            ->where('id', (int) $assignment['asset_id'])
            ->update($this->withTimestamps(SchemaModel::TABLE_ASSETS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSETS, $assetUpdate)));

        return $this->success([
            'assignment' => $this->rowById(SchemaModel::TABLE_ASSET_ASSIGNMENTS, $assignmentId),
            'asset'      => $this->rowById(SchemaModel::TABLE_ASSETS, (int) $assignment['asset_id']),
        ], 'Asset returned successfully.');
    }

    public function maintenanceLogs(int $assetId)
    {
        if ($this->rowById(SchemaModel::TABLE_ASSETS, $assetId) === null) {
            return $this->notFound('Asset not found.');
        }

        return $this->success([
            'maintenance_logs' => $this->db->table(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS)->where('asset_id', $assetId)->orderBy('id', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function addMaintenanceLog(int $assetId)
    {
        if ($this->rowById(SchemaModel::TABLE_ASSETS, $assetId) === null) {
            return $this->notFound('Asset not found.');
        }

        $payload = $this->payload(['performed_by', 'description', 'cost', 'maintenance_date', 'remarks']);
        $errors  = $this->requireFields($payload, ['description']);

        if ($errors !== []) {
            return $this->badRequest('Please provide maintenance details.', $errors);
        }

        $payload['asset_id'] = $assetId;

        if (! isset($payload['maintenance_date']) && $this->tableHasColumn(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS, 'maintenance_date')) {
            $payload['maintenance_date'] = date('Y-m-d');
        }

        $insert = $this->withTimestamps(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS, $this->onlyExistingColumns(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS, $payload), true);

        $this->db->table(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS)->insert($insert);
        $id = (int) $this->db->insertID();

        return $this->created([
            'maintenance_log' => $this->rowById(SchemaModel::TABLE_ASSET_MAINTENANCE_LOGS, $id),
        ], 'Maintenance log added successfully.');
    }
}
