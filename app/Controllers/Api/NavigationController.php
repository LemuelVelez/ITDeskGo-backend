<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\SchemaModel;

class NavigationController extends BaseApiController
{
    public function index()
    {
        return $this->success([
            'auth_stack' => [
                ['key' => 'splash', 'label' => 'Splash Screen', 'route' => '/splash'],
                ['key' => 'login', 'label' => 'Login Screen', 'route' => '/login'],
                ['key' => 'forgot_password', 'label' => 'Forgot Password Screen', 'route' => '/forgot-password'],
            ],
            'role_tabs' => [
                SchemaModel::ROLE_EMPLOYEE => $this->employeeTabs(),
                SchemaModel::ROLE_IT_STAFF => $this->itStaffTabs(),
                SchemaModel::ROLE_ADMIN    => $this->adminTabs(),
            ],
        ]);
    }

    public function tabs(string $role)
    {
        $role = strtolower(trim($role));

        $tabs = match ($role) {
            SchemaModel::ROLE_ADMIN, SchemaModel::ROLE_SUPERADMIN => $this->adminTabs(),
            SchemaModel::ROLE_IT_STAFF => $this->itStaffTabs(),
            default => $this->employeeTabs(),
        };

        return $this->success([
            'role' => $role,
            'tabs' => $tabs,
        ]);
    }

    private function employeeTabs(): array
    {
        return [
            ['key' => 'home', 'label' => 'Home', 'route' => '/employee/home'],
            ['key' => 'tickets', 'label' => 'Tickets', 'route' => '/employee/tickets'],
            ['key' => 'knowledge_base', 'label' => 'Knowledge Base', 'route' => '/employee/knowledge-base'],
            ['key' => 'assets', 'label' => 'Assets', 'route' => '/employee/assets'],
            ['key' => 'profile', 'label' => 'Profile', 'route' => '/employee/profile'],
        ];
    }

    private function itStaffTabs(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => '/it/dashboard'],
            ['key' => 'tickets', 'label' => 'Tickets', 'route' => '/it/tickets'],
            ['key' => 'assets', 'label' => 'Assets', 'route' => '/it/assets'],
            ['key' => 'knowledge_base', 'label' => 'Knowledge Base', 'route' => '/it/knowledge-base'],
            ['key' => 'profile', 'label' => 'Profile', 'route' => '/it/profile'],
        ];
    }

    private function adminTabs(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => '/admin/dashboard'],
            ['key' => 'users', 'label' => 'Users', 'route' => '/admin/users'],
            ['key' => 'tickets', 'label' => 'Tickets', 'route' => '/admin/tickets'],
            ['key' => 'assets', 'label' => 'Assets', 'route' => '/admin/assets'],
            ['key' => 'settings', 'label' => 'Settings', 'route' => '/admin/settings'],
        ];
    }
}
