<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SchemaModel extends Model
{
    public const TABLE_ROLES                  = 'roles';
    public const TABLE_DEPARTMENTS            = 'departments';
    public const TABLE_USERS                  = 'users';
    public const TABLE_CATEGORIES             = 'categories';
    public const TABLE_PRIORITIES             = 'priorities';
    public const TABLE_SLA_RULES              = 'sla_rules';
    public const TABLE_TICKETS                = 'tickets';
    public const TABLE_TICKET_COMMENTS        = 'ticket_comments';
    public const TABLE_TICKET_STATUS_LOGS     = 'ticket_status_logs';
    public const TABLE_KB_CATEGORIES          = 'kb_categories';
    public const TABLE_KB_ARTICLES            = 'kb_articles';
    public const TABLE_ASSET_TYPES            = 'asset_types';
    public const TABLE_ASSETS                 = 'assets';
    public const TABLE_ASSET_ASSIGNMENTS      = 'asset_assignments';
    public const TABLE_ASSET_MAINTENANCE_LOGS = 'asset_maintenance_logs';

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN      = 'admin';
    public const ROLE_IT_STAFF   = 'it_staff';
    public const ROLE_EMPLOYEE   = 'employee';

    public const USER_STATUS_ACTIVE    = 'active';
    public const USER_STATUS_INACTIVE  = 'inactive';
    public const USER_STATUS_SUSPENDED = 'suspended';

    public const TICKET_STATUS_OPEN        = 'open';
    public const TICKET_STATUS_IN_PROGRESS = 'in_progress';
    public const TICKET_STATUS_ON_HOLD     = 'on_hold';
    public const TICKET_STATUS_RESOLVED    = 'resolved';
    public const TICKET_STATUS_CLOSED      = 'closed';
    public const TICKET_STATUS_CANCELLED   = 'cancelled';

    public const KB_STATUS_DRAFT     = 'draft';
    public const KB_STATUS_PUBLISHED = 'published';
    public const KB_STATUS_ARCHIVED  = 'archived';

    public const ASSET_STATUS_AVAILABLE      = 'available';
    public const ASSET_STATUS_ASSIGNED       = 'assigned';
    public const ASSET_STATUS_MAINTENANCE    = 'maintenance';
    public const ASSET_STATUS_RETIRED        = 'retired';
    public const ASSET_STATUS_LOST_OR_STOLEN = 'lost_or_stolen';

    protected $table         = self::TABLE_USERS;
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [];

    public static function roles(): array
    {
        return [
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN      => 'Admin',
            self::ROLE_IT_STAFF   => 'IT Staff',
            self::ROLE_EMPLOYEE   => 'Employee',
        ];
    }

    public static function userStatuses(): array
    {
        return [
            self::USER_STATUS_ACTIVE,
            self::USER_STATUS_INACTIVE,
            self::USER_STATUS_SUSPENDED,
        ];
    }

    public static function ticketStatuses(): array
    {
        return [
            self::TICKET_STATUS_OPEN,
            self::TICKET_STATUS_IN_PROGRESS,
            self::TICKET_STATUS_ON_HOLD,
            self::TICKET_STATUS_RESOLVED,
            self::TICKET_STATUS_CLOSED,
            self::TICKET_STATUS_CANCELLED,
        ];
    }

    public static function kbStatuses(): array
    {
        return [
            self::KB_STATUS_DRAFT,
            self::KB_STATUS_PUBLISHED,
            self::KB_STATUS_ARCHIVED,
        ];
    }

    public static function assetStatuses(): array
    {
        return [
            self::ASSET_STATUS_AVAILABLE,
            self::ASSET_STATUS_ASSIGNED,
            self::ASSET_STATUS_MAINTENANCE,
            self::ASSET_STATUS_RETIRED,
            self::ASSET_STATUS_LOST_OR_STOLEN,
        ];
    }

    public static function tables(): array
    {
        return [
            'auth' => [
                self::TABLE_ROLES,
                self::TABLE_DEPARTMENTS,
                self::TABLE_USERS,
            ],
            'tickets' => [
                self::TABLE_CATEGORIES,
                self::TABLE_PRIORITIES,
                self::TABLE_SLA_RULES,
                self::TABLE_TICKETS,
                self::TABLE_TICKET_COMMENTS,
                self::TABLE_TICKET_STATUS_LOGS,
            ],
            'knowledge_base' => [
                self::TABLE_KB_CATEGORIES,
                self::TABLE_KB_ARTICLES,
            ],
            'assets' => [
                self::TABLE_ASSET_TYPES,
                self::TABLE_ASSETS,
                self::TABLE_ASSET_ASSIGNMENTS,
                self::TABLE_ASSET_MAINTENANCE_LOGS,
            ],
        ];
    }
}
