<?php

return [
    'roles' => [
        'super-admin' => 'Super Admin',
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'staff' => 'Staff',
        'user' => 'User',
    ],
    'permissions' => [
        // Dashboard
        'dashboard.view' => 'View dashboard',

        // Users Management
        'users.view' => 'View users',
        'users.create' => 'Create users',
        'users.update' => 'Update users',
        'users.delete' => 'Delete users',
        'users.restore' => 'Restore deleted users',
        'users.force-delete' => 'Permanently delete users',

        // Products Management
        'products.view' => 'View products',
        'products.create' => 'Create products',
        'products.update' => 'Update products',
        'products.delete' => 'Delete products',
        'products.restore' => 'Restore deleted products',

        // Settings
        'settings.view' => 'View settings',
        'settings.update' => 'Update settings',
        'settings.manage-system' => 'Manage system settings',

        // Reports
        'reports.view' => 'View reports',
        'reports.export' => 'Export reports',
        'reports.financial' => 'View financial reports',

        // Logs & Audit
        'logs.view' => 'View system logs',
        'logs.delete' => 'Delete system logs',
        'audit.view' => 'View audit trail',

        // Roles & Permissions
        'roles.view' => 'View roles',
        'roles.create' => 'Create roles',
        'roles.update' => 'Update roles',
        'roles.delete' => 'Delete roles',
        'permissions.assign' => 'Assign permissions',
    ],

    'users' => [
        'initial_users' => [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@mail.com',
                'password' => env('SUPERADMIN_PASSWORD', env('DEFAULT_USER_PASSWORD') ?: 'power@123'),
                'email_verified_at' => now(),
                'roles' => ['super-admin'], // Spatie role
                'permissions' => [],
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@mail.com',
                'password' => env('ADMIN_PASSWORD', env('DEFAULT_USER_PASSWORD') ?: 'power@123'),
                'email_verified_at' => now(),
                'roles' => ['admin'],
                'permissions' => [],
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@mail.com',
                'password' => env('MANAGER_PASSWORD', env('DEFAULT_USER_PASSWORD') ?: 'power@123'),
                'email_verified_at' => now(),
                'roles' => ['manager'],
                'permissions' => [],
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@mail.com',
                'password' => env('STAFF_PASSWORD', env('DEFAULT_USER_PASSWORD') ?: 'power@123'),
                'email_verified_at' => now(),
                'roles' => ['staff'],
                'permissions' => [],
            ],
        ]
    ]
];
